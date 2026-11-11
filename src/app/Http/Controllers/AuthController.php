<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Rules\NonDisposableEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->merge([
            'name' => trim((string) $request->input('name')),
            'email' => strtolower(trim((string) $request->input('email'))),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email', new NonDisposableEmail()],
            'password' => ['required', 'string', 'confirmed', 'min:8', 'max:128'],
            'company_name' => ['prohibited'],
            'contact_person' => ['prohibited'],
            'phone' => ['prohibited'],
            'customer_id' => ['prohibited'],
            'role' => ['prohibited'],
            'account_type' => ['prohibited'],
            'country_code' => ['prohibited'],
            'city' => ['prohibited'],
            'phone_country_code' => ['prohibited'],
            'email_verified_at' => ['prohibited'],
            'phone_verified_at' => ['prohibited'],
            'phone_verification_required' => ['prohibited'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone_verification_required' => true,
        ]);

        try {
            $user->notify(new \App\Notifications\AdminPlatformNotification(
                type: 'account.welcome',
                title: 'Welcome to HAG Brokers',
                message: 'Welcome '.$user->name.'! Your personal account has been created successfully.',
                url: '/dashboard',
                extra: ['action' => 'complete_company_setup']
            ));
        } catch (\Throwable $exception) {
            report($exception);
        }

        try {
            \App\Services\NotificationService::admins(
                type: 'user.created',
                title: 'New User Registered',
                message: $user->name.' has registered a personal account.',
                model: $user,
                data: ['user_id' => $user->id, 'url' => '/admin/users']
            );
        } catch (\Throwable $exception) {
            report($exception);
        }

        if (config('welcome_mail.enabled', false)) {
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)
                    ->queue(new \App\Mail\WelcomeAccountMail($user->name));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        try {
            $user->sendEmailVerificationNotification();
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()
            ->route('login')
            ->with('status', __('auth.verification_email_sent'));
    }
}
