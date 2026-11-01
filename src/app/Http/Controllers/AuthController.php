<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $validated = $request->validate([
            'company_name' => [
                'required',
                'string',
                'max:255',
            ],

            'contact_person' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:50',
            ],

            'country_code' => [
                'required',
                'string',
                'size:2',
            ],

            'city' => [
                'nullable',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'password' => [
                'required',
                'confirmed',
                'min:8',
            ],
        ]);

        $user = DB::transaction(function () use ($validated) {

            /*
             * 1. Create Customer
             */
            $customer = Customer::create([
                'company_name' => $validated['company_name'],
                'contact_person' => $validated['contact_person'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'country_code' => strtoupper($validated['country_code']),
                'city' => $validated['city'] ?? null,
                'is_active' => true,
            ]);

            /*
             * 2. Create User
             */
            $user = User::create([
                'name' => $validated['contact_person'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'customer_id' => $customer->id,
                'role' => 'customer',
                'phone' => $validated['phone'],
            ]);

            return $user;
        });

        /*
         * 3. Welcome notification after successful registration.
         */
        $user->notify(new \App\Notifications\AdminPlatformNotification(
            type: 'account.welcome',
            title: 'Welcome to HAG Brokers',
            message: 'Welcome ' . $user->name . '! Your account has been created successfully.',
            url: '/dashboard',
            extra: ['action' => 'complete_profile']
        ));

        /*
         * Notify administrators after successful registration.
         */
        try {
            \App\Services\NotificationService::admins(
                type: 'user.created',
                title: 'New User Registered',
                message: $user->name . ' has registered a new account.',
                model: $user,
                data: [
                    'user_id' => $user->id,
                    'url' => '/admin/users',
                ]
            );
        } catch (\Throwable $exception) {
            report($exception);
        }

        /*
         * 4. Login user automatically
         */
        if (config('welcome_mail.enabled', false)) {
            try {
                \Illuminate\Support\Facades\Mail::to($user->email)
                    ->queue(new \App\Mail\WelcomeAccountMail(
                        $user->name
                    ));
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                'Your HAG Brokers account has been created successfully.'
            );
    }
}
