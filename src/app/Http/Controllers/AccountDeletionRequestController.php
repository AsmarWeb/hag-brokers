<?php

namespace App\Http\Controllers;

use App\Services\AccountDeletion\AccountDeletionRequestVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class AccountDeletionRequestController extends Controller
{
    /**
     * Display the public account-deletion request form.
     */
    public function create(): View
    {
        return view('account-deletion.request', [
            'language' => app()->getLocale(),
        ]);
    }

    /**
     * Request email verification without revealing whether the account exists.
     */
    public function store(
        Request $request,
        AccountDeletionRequestVerificationService $service
    ): RedirectResponse {
        $request->merge([
            'email' => Str::lower(trim((string) $request->input('email'))),
        ]);

        $data = $request->validate([
            'email' => ['required', 'string', 'email', 'max:254'],
        ]);

        $service->sendVerification(
            $data['email'],
            app()->getLocale()
        );

        // This message is identical for registered and unregistered addresses.
        $status = app()->getLocale() === 'ar'
            ? 'إذا كان البريد الإلكتروني مرتبطاً بحساب، فستصلك رسالة تتضمن خطوات التحقق.'
            : 'If this email address is associated with an account, you will receive an email with verification instructions.';

        return redirect()
            ->route('account-deletion.request')
            ->with('status', $status);
    }

    /**
     * Display the explicit email-ownership confirmation page.
     * Opening the link does not consume the token.
     */
    public function showVerification(
        string $token,
        AccountDeletionRequestVerificationService $service
    ): View {
        abort_unless($service->isValid($token), 410);

        return view('account-deletion.request-verify', [
            'token' => $token,
            'language' => app()->getLocale(),
        ]);
    }

    /**
     * Consume the token and submit the deletion request.
     */
    public function verify(
        Request $request,
        string $token,
        AccountDeletionRequestVerificationService $service
    ): View {
        $verified = $service->verify($token);

        abort_unless($verified, 410);

        return view('account-deletion.request-submitted', [
            'language' => app()->getLocale(),
        ]);
    }
}
