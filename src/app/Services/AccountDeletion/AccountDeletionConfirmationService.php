<?php

namespace App\Services\AccountDeletion;

use App\Mail\AccountDeletionConfirmationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;

final class AccountDeletionConfirmationService
{
    public function send(int $requestId, string $language = 'en'): void
    {
        $language = in_array($language, ['ar', 'en'], true)
            ? $language
            : 'en';

        $token = Str::random(64);
        $expiresAt = now()->addHours(24);

        $request = DB::transaction(function () use ($requestId, $token, $expiresAt): object {
            $deletionRequest = DB::table('account_deletion_requests')
                ->where('id', $requestId)
                ->lockForUpdate()
                ->first();

            if (! $deletionRequest) {
                throw new RuntimeException('Account deletion request not found.');
            }

            if (! in_array($deletionRequest->status, ['reviewing', 'confirmation_sent'], true)) {
                throw new RuntimeException('Account deletion request is not ready for confirmation.');
            }

            $user = $deletionRequest->user_id
                ? DB::table('users')->where('id', $deletionRequest->user_id)->first()
                : null;

            $email = $user?->email ?: $deletionRequest->requested_email;

            if (! $email) {
                throw new RuntimeException('No email address is available for this request.');
            }

            DB::table('account_deletion_requests')
                ->where('id', $requestId)
                ->update([
                    'requested_email' => $email,
                    'confirmation_token_hash' => hash('sha256', $token),
                    'confirmation_expires_at' => $expiresAt,
                    'confirmed_at' => null,
                    'email_sent_at' => null,
                    'status' => 'confirmation_sent',
                    'version' => $deletionRequest->version + 1,
                    'updated_at' => now(),
                ]);

            return (object) [
                'email' => $email,
                'expires_at' => $expiresAt,
            ];
        });

        $confirmationUrl = route('account-deletion.confirm', [
            'token' => $token,
        ]);

        try {
            Mail::to($request->email)->send(
                new AccountDeletionConfirmationMail(
                    confirmationUrl: $confirmationUrl,
                    expiresAt: $request->expires_at->format('Y-m-d H:i') . ' UTC',
                    language: $language,
                )
            );

            DB::table('account_deletion_requests')
                ->where('id', $requestId)
                ->where('confirmation_token_hash', hash('sha256', $token))
                ->update([
                    'email_sent_at' => now(),
                    'updated_at' => now(),
                ]);
        } catch (Throwable $exception) {
            DB::table('account_deletion_requests')
                ->where('id', $requestId)
                ->where('confirmation_token_hash', hash('sha256', $token))
                ->update([
                    'confirmation_token_hash' => null,
                    'confirmation_expires_at' => null,
                    'status' => 'reviewing',
                    'version' => DB::raw('version + 1'),
                    'updated_at' => now(),
                ]);

            throw $exception;
        }
    }
}
