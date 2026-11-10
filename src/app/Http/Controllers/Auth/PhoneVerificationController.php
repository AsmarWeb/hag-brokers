<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\PhoneVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class PhoneVerificationController extends Controller
{
    public function notice(Request $request): View
    {
        return view('auth.verify-phone', [
            'email' => $request->user()?->email ?? $request->query('email', old('email')),
        ]);
    }

    public function resend(Request $request, PhoneVerificationService $verification): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'string', 'email', 'max:255'],
        ]);

        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $user = $email !== ''
            ? User::query()->where('email', $email)->first()
            : $request->user();

        if ($user && ! $user->hasVerifiedPhone()) {
            $verification->send($user);
        }

        $message = __('auth.phone_code_sent');

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 202);
        }

        return back()->with('status', $message);
    }

    public function verify(Request $request, PhoneVerificationService $verification): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'code' => ['required', 'digits:6'],
        ]);

        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $user = $email !== ''
            ? User::query()->where('email', $email)->first()
            : $request->user();

        if (! $user || ! $verification->verify($user, $data['code'])) {
            throw ValidationException::withMessages([
                'code' => __('auth.phone_code_invalid'),
            ]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'data' => [
                    'phone_verified' => true,
                    'email_verified' => $user->hasVerifiedEmail(),
                    'account_verified' => $user->hasVerifiedAccount(),
                ],
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('status', __('auth.phone_verified_login'));
        }

        return redirect()->route('login')->with('status', __('auth.phone_verified_still_email'));
    }
}
