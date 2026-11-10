<?php

namespace App\Services\Auth;

use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

final class PhoneVerificationService
{
    public function __construct(private readonly TwilioSmsGateway $sms)
    {
    }

    public function send(User $user): bool
    {
        $phone = trim((string) $user->phone);

        if (! preg_match('/^\\+[1-9][0-9]{7,14}$/D', $phone)) {
            return false;
        }

        if ($user->phone_verified_at !== null) {
            return true;
        }

        $limiterKey = 'phone-verification-send:'.$user->getKey();

        if (RateLimiter::tooManyAttempts($limiterKey, 3)) {
            return false;
        }

        $existing = DB::table('phone_verifications')->where('user_id', $user->getKey())->first();

        if ($existing && Carbon::parse($existing->sent_at)->gt(now()->subSeconds((int) config('auth_security.phone_otp_resend_cooldown_seconds', 60)))) {
            return false;
        }

        RateLimiter::hit($limiterKey, 3600);

        $code = (string) random_int(100000, 999999);
        $now = now();

        DB::table('phone_verifications')->updateOrInsert(
            ['user_id' => $user->getKey()],
            [
                'phone' => $phone,
                'code_hash' => Hash::make($code),
                'attempts' => 0,
                'expires_at' => $now->copy()->addMinutes((int) config('auth_security.phone_otp_ttl_minutes', 10)),
                'sent_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );

        $message = app()->getLocale() === 'ar'
            ? "رمز تأكيد HAG Brokers هو {$code}. صالح لمدة 10 دقائق. لا تشاركه مع أي شخص."
            : "Your HAG Brokers verification code is {$code}. It expires in 10 minutes. Do not share this code.";

        if (! $this->sms->send($phone, $message)) {
            DB::table('phone_verifications')->where('user_id', $user->getKey())->delete();

            return false;
        }

        return true;
    }

    public function verify(User $user, string $code): bool
    {
        return DB::transaction(function () use ($user, $code): bool {
            // Lock and re-read the authoritative user row before trusting the submitted model.
            // This serializes verification against concurrent phone-number changes.
            $lockedUser = User::query()
                ->whereKey($user->getKey())
                ->lockForUpdate()
                ->first();

            if (! $lockedUser || $lockedUser->phone === null) {
                return false;
            }

            $challenge = DB::table('phone_verifications')
                ->where('user_id', $lockedUser->getKey())
                ->lockForUpdate()
                ->first();

            if (
                ! $challenge
                || ! hash_equals((string) $lockedUser->phone, (string) $challenge->phone)
                || Carbon::parse($challenge->expires_at)->isPast()
                || (int) $challenge->attempts >= (int) config('auth_security.phone_otp_max_attempts', 5)
            ) {
                return false;
            }

            if (! Hash::check($code, $challenge->code_hash)) {
                DB::table('phone_verifications')
                    ->where('user_id', $lockedUser->getKey())
                    ->increment('attempts');

                return false;
            }

            $lockedUser->forceFill(['phone_verified_at' => now()])->save();

            DB::table('phone_verifications')->where('user_id', $lockedUser->getKey())->delete();

            RateLimiter::clear('phone-verification-send:'.$lockedUser->getKey());

            return true;
        }, 3);
    }
}
