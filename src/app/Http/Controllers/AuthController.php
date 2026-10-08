<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Show registration form.
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Handle registration.
     */
    public function register(Request $request): RedirectResponse
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
            |--------------------------------------------------------------------------
            | Create Customer
            |--------------------------------------------------------------------------
            */

            $customer = Customer::create([
                'company_name'   => $validated['company_name'],
                'contact_person' => $validated['contact_person'],
                'email'          => $validated['email'],
                'phone'          => $validated['country_code'] . ' ' . $validated['phone'],
                'country_code'   => strtoupper($validated['country_code']),
                'city'           => $validated['city'] ?? null,
                'is_active'      => true,
            ]);


            /*
            |--------------------------------------------------------------------------
            | Create User
            |--------------------------------------------------------------------------
            */

            $user = User::create([
                'name'        => $validated['contact_person'],
                'email'       => $validated['email'],
                'password'    => $validated['password'],
                'customer_id' => $customer->id,
                'role'        => 'customer',
                'phone'       => $validated['country_code'] . ' ' . $validated['phone'],
            ]);

            return $user;
        });


        /*
        |--------------------------------------------------------------------------
        | Login
        |--------------------------------------------------------------------------
        */

        Auth::login($user);

        $request->session()->regenerate();


        /*
        |--------------------------------------------------------------------------
        | Redirect
        |--------------------------------------------------------------------------
        */

        return redirect()
            ->route('dashboard')
            ->with(
                'success',
                'Your HAG Brokers account has been created successfully.'
            );
    }
}
