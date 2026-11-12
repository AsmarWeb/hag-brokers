<?php

namespace App\Services\Auth;

use App\Models\User;
use App\Notifications\PhoneVerificationCode;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;

final class PhoneVerificationService
{
    public function send(User $user): bool
    {
        $phone = trim((string) $user->phone);
        $email = trim((string) $user->email);

        // The code is sent only to the account's verified email address.
        if (
            ! $user->hasVerifiedEmail()
            || ! filter_var($email, FILTER_VALIDATE_EMAIL)
        ) {
            return false;
        }

        if (! preg_match('/^\+[1-9][0-9]{7,14}$/D', $phone)) {
            return false;
        }

        if ($user->phone_verified_at !== null) {
            return true;
        }

        $limiterKey = 'phone-verification-send:'.$user->getKey();

        if (RateLimiter::tooManyAttempts($limiterKey, 3)) {
            return false;
        }

        $existing = DB::table('phone_verifications')
            ->where('user_id', $user->getKey())
            ->first();

        $cooldown = (int) config(
            'auth_security.phone_otp_resend_cooldown_seconds',
            60
        );

        if (
            $existing
            && Carbon::parse($existing->sent_at)->gt(now()->subSeconds($cooldown))
        ) {
            return false;
        }

        $ttl = (int) config('auth_security.phone_otp_ttl_minutes', 10);
        $code = (string) random_int(100000, 999999);
        $now = now();

        RateLimiter::hit($limiterKey, 3600);

        DB::table('phone_verifications')->updateOrInsert(
            ['user_id' => $user->getKey()],
            [
                'phone' => $phone,
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => $now->copy()->addMinutes($ttl),
                'sent_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        try {
            Notification::route('mail', $email)
                ->notify(new PhoneVerificationCode($code, $ttl));
        } catch (\Throwable $exception) {
            DB::table('phone_verifications')
                ->where('user_id', $user->getKey())
                ->delete();

            throw $exception;
        }

        return true;
    }

    public function sendSafely(User $user): bool
    {
        try {
            return $this->send($user);
        } catch (\Throwable $exception) {
            Log::error('Phone confirmation email delivery could not be completed.', [
                'user_id' => $user->getKey(),
                'exception' => $exception::class,
            ]);

            return false;
        }
    }

    public function verify(User $user, string $code): bool
    {
        return DB::transaction(function () use ($user, $code): bool {
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->first();

            if (
                ! $lockedUser
                || ! $lockedUser->hasVerifiedEmail()
                || $lockedUser->phone === null
            ) {
                return false;
            }

            $challenge = DB::table('phone_verifications')
                ->where('user_id', $lockedUser->getKey())
                ->lockForUpdate()
                ->first();

            if (
                ! $challenge
                || ! hash_equals((string) $lockedUser->phone, (string) $challenge->phone)
                || Carbon::parse($challenge->expires_at)->isPast()
                || (int) $challenge->attempts >= (int) config('auth_security.phone_otp_max_attempts', 5)
            ) {
                return false;
            }

            if (! Hash::check($code, $challenge->code_hash)) {
                DB::table('phone_verifications')
                    ->where('user_id', $lockedUser->getKey())
                    ->increment('attempts');

                return false;
            }

            // Email confirmation completes the account setup step.
            // It does NOT prove ownership of the phone number.
            $lockedUser->forceFill([
                'phone_verification_required' => false,
            ])->save();

            DB::table('phone_verifications')
                ->where('user_id', $lockedUser->getKey())
                ->delete();

            RateLimiter::clear('phone-verification-send:'.$lockedUser->getKey());

            return true;
        }, 3);
    }
}
