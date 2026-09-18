<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
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
                'lowercase',
                'email',
                'max:255',
                'unique:' . User::class,
            ],

            'password' => [
                'required',
                'confirmed',
                Rules\Password::defaults(),
            ],
        ]);

        $user = DB::transaction(function () use ($validated) {

            // 1. Create customer
            $customer = Customer::create([
                'company_name' => $validated['company_name'],
                'contact_person' => $validated['contact_person'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'country_code' => strtoupper($validated['country_code']),
                'city' => $validated['city'] ?? null,
                'is_active' => true,
            ]);

            // 2. Create user
            return User::create([
                'name' => $validated['contact_person'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'customer_id' => $customer->id,
                'role' => 'customer',
                'phone' => $validated['phone'],
            ]);
        });

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        return redirect(
            route('dashboard', absolute: false)
        );
    }
}
