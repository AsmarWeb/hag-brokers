<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use App\Rules\NonDisposableEmail;
use App\Services\Auth\PhoneVerificationService;
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
            'email' => strtolower(trim((string) $request->input('email'))),
            'phone' => PhoneNumber::normalize($request->input('phone'), $request->input('phone_country_code')),
        ]);

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

            'phone_country_code' => ['required', 'string', \\Illuminate\\Validation\\Rule::in(array_keys(config('phone.countries', [])))],
            'phone' => ['required', 'string', 'regex:/^\\+[1-9][0-9]{7,14}$/D'],

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
                new NonDisposableEmail(),
            ],

            'password' => [
                'required',
                'string',
                'confirmed',
                'min:12',
                'regex:/[A-Z]/',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
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

            app(\App\Services\Platform\CompanyWorkspaceService::class)->activateNewRegistration($customer, $user);

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
