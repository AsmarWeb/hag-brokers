<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

final class AccountDeletionConfirmationController extends Controller
{
    public function show(string $token): View
    {
        $request = DB::table('account_deletion_requests')
            ->where('confirmation_token_hash', hash('sha256', $token))
            ->where('status', 'confirmation_sent')
            ->whereNotNull('email_sent_at')
            ->whereNull('confirmed_at')
            ->where('confirmation_expires_at', '>', now())
            ->first();

        abort_unless($request, 410);

        return view('account-deletion.confirm', [
            'token' => $token,
            'language' => in_array(request()->query('lang'), ['ar', 'en'], true)
                ? request()->query('lang')
                : app()->getLocale(),
        ]);
    }

    public function confirm(Request $request, string $token): View
    {
        $hash = hash('sha256', $token);

        $confirmed = DB::transaction(function () use ($hash): bool {
            $deletionRequest = DB::table('account_deletion_requests')
                ->where('confirmation_token_hash', $hash)
                ->lockForUpdate()
                ->first();

            if (
                ! $deletionRequest
                || $deletionRequest->status !== 'confirmation_sent'
                || ! $deletionRequest->email_sent_at
                || $deletionRequest->confirmed_at
                || ! $deletionRequest->confirmation_expires_at
                || now()->greaterThanOrEqualTo($deletionRequest->confirmation_expires_at)
            ) {
                return false;
            }

            DB::table('account_deletion_requests')
                ->where('id', $deletionRequest->id)
                ->update([
                    'status' => 'email_confirmed',
                    'confirmed_at' => now(),
                    'confirmation_token_hash' => null,
                    'confirmation_expires_at' => null,
                    'version' => $deletionRequest->version + 1,
                    'updated_at' => now(),
                ]);

            return true;
        });

        abort_unless($confirmed, 410);

        $language = in_array($request->input('lang'), ['ar', 'en'], true)
            ? $request->input('lang')
            : app()->getLocale();

        return view('account-deletion.confirmed', [
            'language' => $language,
        ]);
    }
}
