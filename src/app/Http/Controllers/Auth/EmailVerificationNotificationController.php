<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'email' => ['nullable', 'string', 'email', 'max:255'],
        ]);

        $email = strtolower(trim((string) ($data['email'] ?? '')));
        $user = $email !== ''
            ? User::query()->where('email', $email)->first()
            : $request->user();

        if ($user && ! $user->hasVerifiedEmail()) {
            try {
                $user->sendEmailVerificationNotification();
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return back()->with('status', __('auth.verification_email_sent'));
    }
}
