<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\MobileVerifyEmail;
use App\Rules\NonDisposableEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'max:128'],
        ]);

        $user = User::where('email', $data['email'])->first();

        if (
            ! $user
            || ! Hash::check($data['password'], $user->password)
            || ! $user->hasVerifiedAccount()
            || NonDisposableEmail::isDisposable($data['email'])
        ) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }

        if ($user->hasRole(['admin', 'super-admin'])) {
            return response()->json([
                'success' => false,
                'message' => 'Platform administrator accounts use the web dashboard.',
            ], 403);
        }

        $expiresAt = now()->addDays(30);
        $token = $user->createToken('mobile:legacy', ['mobile:v1'], $expiresAt);

        return response()->json([
            'success' => true,
            'token' => $token->plainTextToken,
            'expires_at' => $expiresAt->utc()->toIso8601String(),
            'user' => MobileAuthController::identity($user),
        ]);
    }

    public function register(Request $request)
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', new NonDisposableEmail()],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
            'company_name' => ['required', 'string', 'max:255'],
        ]);

        $customer = Customer::create(['company_name' => $data['company_name']]);
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'customer_id' => $customer->id,
            'role' => 'customer',
        ]);

        try {
            $user->notify(new MobileVerifyEmail);
        } catch (\Throwable $exception) {
            report($exception);
        }

        return response()->json([
            'success' => true,
            'message' => 'Email verification is required before signing in.',
            'data' => ['email' => $user->email, 'verification_required' => true],
        ], 202);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['success' => true]);
    }
}
