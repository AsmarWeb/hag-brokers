<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\PhoneNumber;
use App\Services\Auth\PhoneVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class PhoneVerificationController extends Controller
{
    public function notice(Request $request): View
    {
        return view('auth.verify-phone', [
            'phone' => $request->user()->phone,
            'phoneCountryCode' => PhoneNumber::countryFor($request->user()->phone),
            'phoneVerified' => $request->user()->hasVerifiedPhone(),
        ]);
    }

    public function updatePhone(Request $request, PhoneVerificationService $verification): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $request->merge([
            'phone' => PhoneNumber::normalize($request->input('phone'), $request->input('phone_country_code')),
        ]);
        $data = $request->validate([
            'phone_country_code' => ['required', 'string', \Illuminate\Validation\Rule::in(array_keys(config('phone.countries', [])))],
            'phone' => ['required', 'string', 'regex:/^\+[1-9][0-9]{7,14}$/D'],
            'current_password' => ['nullable', 'string', 'max:128'],
        ]);

        $user = DB::transaction(function () use ($request, $data): User {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            $changed = $user->phone !== $data['phone'];
            if ($changed && $user->hasVerifiedPhone() && ! Hash::check($data['current_password'] ?? '', $user->password)) {
                throw ValidationException::withMessages([
                    'current_password' => [app()->getLocale() === 'ar' ? 'أدخل كلمة المرور الحالية لتغيير رقم موثّق.' : 'Enter your current password to change a verified phone number.'],
                ]);
            }
            if ($changed) {
                $user->forceFill([
                    'phone' => $data['phone'],
                    'phone_verified_at' => null,
                    'phone_verification_required' => true,
                ])->save();
                DB::table('phone_verifications')->where('user_id', $user->id)->delete();
            }

            return $user;
        }, 3);

        $sent = $user->hasVerifiedPhone() || $verification->sendSafely($user);
        if ($request->expectsJson()) {
            return response()->json(['data' => [
                'phone' => $user->phone,
                'phone_verified' => $user->hasVerifiedPhone(),
                'verification_sent' => $sent,
            ]], 202);
        }

        return back()->with('status', $sent ? __('auth.phone_code_sent') : __('auth.phone_code_delivery_unavailable'));
    }

    public function resend(Request $request, PhoneVerificationService $verification): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        abort_unless($user->phone, 422, 'Add a phone number first.');
        $sent = $user->hasVerifiedPhone() || $verification->sendSafely($user);
        if ($request->expectsJson()) {
            return response()->json(['data' => ['verification_sent' => $sent]], 202);
        }

        return back()->with('status', $sent ? __('auth.phone_code_sent') : __('auth.phone_code_delivery_unavailable'));
    }

    public function verify(Request $request, PhoneVerificationService $verification): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $data = $request->validate(['code' => ['required', 'digits:6']]);
        $user = $request->user();
        if (! $verification->verify($user, $data['code'])) {
            throw ValidationException::withMessages(['code' => [__('auth.phone_code_invalid')]]);
        }

        if ($request->expectsJson()) {
            return response()->json(['data' => [
                'phone_verified' => true,
                'email_verified' => $user->fresh()->hasVerifiedEmail(),
                'account_verified' => $user->fresh()->hasVerifiedAccount(),
            ]]);
        }

        return redirect()->route('dashboard')->with('status', __('auth.phone_verified_login'));
    }
}
