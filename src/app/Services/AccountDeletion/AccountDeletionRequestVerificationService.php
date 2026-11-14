<?php

namespace App\Services\AccountDeletion;

use App\Mail\AccountDeletionRequestVerificationMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

final class AccountDeletionRequestVerificationService
{
    /**
     * Send a public email-ownership verification link.
     *
     * The response is deliberately identical for existing and unknown emails.
     * No deletion request is created until the recipient explicitly verifies.
     */
    public function sendVerification(string $email, string $language = 'en'): void
    {
        $email = Str::lower(trim($email));

        $language = in_array($language, ['ar', 'en'], true)
            ? $language
            : 'en';

        // This key contains a hash, never the submitted email address.
        $limiterKey = 'account-deletion-request-email:'.hash('sha256', $email);

        if (RateLimiter::tooManyAttempts($limiterKey, 3)) {
            return;
        }

        // Count attempts for both existing and unknown email addresses.
        RateLimiter::hit($limiterKey, 3600);

        $token = Str::random(64);
        $tokenHash = hash('sha256', $token);
        $expiresAt = now()->addHour();

        $verification = DB::transaction(function () use (
            $email,
            $tokenHash,
            $expiresAt
        ): ?object {
            $user = User::query()
                ->whereRaw('LOWER(email) = ?', [$email])
                ->lockForUpdate()
                ->first();

            if (! $user) {
                return null;
            }

            // Never replace, duplicate, or silently restart an existing request.
            $hasDeletionRequest = DB::table('account_deletion_requests')
                ->where('user_id', $user->getKey())
                ->exists();

            if ($hasDeletionRequest) {
                return null;
            }

            $existing = DB::table('account_deletion_email_verifications')
                ->where('user_id', $user->getKey())
                ->lockForUpdate()
                ->first();

            // Avoid invalidating a link that was just sent.
            $cooldownSeconds = 60;

            if (
                $existing
                && $existing->email_sent_at
                && now()->lessThan(
                    \Illuminate\Support\Carbon::parse($existing->email_sent_at)
                        ->addSeconds($cooldownSeconds)
                )
            ) {
                return null;
            }

            if ($existing && $existing->verified_at) {
                return null;
            }

            $now = now();

            $values = [
                'email' => $user->email,
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt,
                'email_sent_at' => null,
                'verified_at' => null,
                'updated_at' => $now,
            ];

            if ($existing) {
                DB::table('account_deletion_email_verifications')
                    ->where('id', $existing->id)
                    ->update($values);
            } else {
                DB::table('account_deletion_email_verifications')->insert([
                    'user_id' => $user->getKey(),
                    ...$values,
                    'created_at' => $now,
                ]);
            }

            return (object) [
                'user_id' => $user->getKey(),
                'email' => $user->email,
                'expires_at' => $expiresAt,
            ];
        }, 3);

        // Unknown email, existing request, or resend cooldown:
        // return without revealing which condition occurred.
        if (! $verification) {
            return;
        }

        try {
            $verificationUrl = route('account-deletion.request.verify.show', [
                'token' => $token,
                'lang' => $language,
            ]);

            Mail::to($verification->email)->send(
                new AccountDeletionRequestVerificationMail(
                    verificationUrl: $verificationUrl,
                    expiresAt: $verification->expires_at->copy()
                        ->utc()
                        ->format('Y-m-d H:i').' UTC',
                    language: $language,
                )
            );

            DB::table('account_deletion_email_verifications')
                ->where('user_id', $verification->user_id)
                ->where('token_hash', $tokenHash)
                ->update([
                    'email_sent_at' => now(),
                    'updated_at' => now(),
                ]);
        } catch (Throwable $exception) {
            // A token must not remain usable when its email was not sent.
            DB::table('account_deletion_email_verifications')
                ->where('user_id', $verification->user_id)
                ->where('token_hash', $tokenHash)
                ->update([
                    'token_hash' => null,
                    'expires_at' => null,
                    'email_sent_at' => null,
                    'verified_at' => null,
                    'updated_at' => now(),
                ]);

            Log::warning('Public account deletion verification email could not be sent.', [
                'user_id' => $verification->user_id,
                'email_hash' => hash('sha256', $email),
                'exception' => $exception::class,
            ]);
        }
    }

    /**
     * Check whether a token may be displayed for explicit confirmation.
     * A GET request never consumes or verifies the token.
     */
    public function isValid(string $token): bool
    {
        return DB::table('account_deletion_email_verifications')
            ->where('token_hash', hash('sha256', $token))
            ->whereNotNull('email_sent_at')
            ->whereNull('verified_at')
            ->where('expires_at', '>', now())
            ->exists();
    }

    /**
     * Consume the token and create the deletion request atomically.
     */
    public function verify(string $token): bool
    {
        $tokenHash = hash('sha256', $token);

        return DB::transaction(function () use ($tokenHash): bool {
            $verification = DB::table('account_deletion_email_verifications')
                ->where('token_hash', $tokenHash)
                ->lockForUpdate()
                ->first();

            if (
                ! $verification
                || ! $verification->email_sent_at
                || $verification->verified_at
                || ! $verification->expires_at
                || now()->greaterThanOrEqualTo($verification->expires_at)
            ) {
                return false;
            }

            $user = DB::table('users')
                ->where('id', $verification->user_id)
                ->lockForUpdate()
                ->first();

            if (! $user) {
                return false;
            }

            // The verification link must still target the user's current email.
            $currentEmail = Str::lower(trim((string) $user->email));
            $verifiedEmail = Str::lower(trim((string) $verification->email));

            if ($currentEmail === '' || $currentEmail !== $verifiedEmail) {
                DB::table('account_deletion_email_verifications')
                    ->where('id', $verification->id)
                    ->update([
                        'token_hash' => null,
                        'expires_at' => null,
                        'email_sent_at' => null,
                        'updated_at' => now(),
                    ]);

                return false;
            }

            // An API or another channel may have created a request
            // after this verification link was issued.
            $hasDeletionRequest = DB::table('account_deletion_requests')
                ->where('user_id', $verification->user_id)
                ->exists();

            if ($hasDeletionRequest) {
                DB::table('account_deletion_email_verifications')
                    ->where('id', $verification->id)
                    ->update([
                        'token_hash' => null,
                        'updated_at' => now(),
                    ]);

                return false;
            }

            $now = now();

            DB::table('account_deletion_requests')->insert([
                'user_id' => $verification->user_id,
                'requested_email' => $verification->email,
                'status' => 'pending',
                'version' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Keep the verification audit timestamps but consume the token.
            DB::table('account_deletion_email_verifications')
                ->where('id', $verification->id)
                ->update([
                    'token_hash' => null,
                    'verified_at' => $now,
                    'updated_at' => $now,
                ]);

            return true;
        }, 3);
    }
}
