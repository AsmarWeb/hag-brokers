<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\CompanyMember;
use App\Models\CompanyWorkspace;
use App\Models\Customer;
use App\Models\User;
use App\Notifications\MobileVerifyEmail;
use App\Rules\NonDisposableEmail;
use App\Services\Auth\PhoneNumber;
use App\Services\Auth\PhoneVerificationService;
use App\Services\Platform\CompanyMarketplaceService;
use App\Services\Platform\CompanyWorkspaceService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MobileAuthController extends Controller
{
    public static function identity(User $u): array
    {
        $member = CompanyMember::where('user_id', $u->id)->first();
        $workspace = $member ? CompanyWorkspace::find($member->company_workspace_id) : null;

        return ['id' => $u->id, 'name' => $u->name, 'email' => $u->email, 'phone' => $u->phone,
            'email_verified' => $u->hasVerifiedEmail(), 'phone_verified' => $u->hasVerifiedPhone(),
            'account_verified' => $u->hasVerifiedAccount(),
            'account_type' => $member ? 'company' : 'individual',
            'is_carrier' => $u->carrierPartnership()->whereHas('carrier', fn ($q) => $q->where('is_active', true))->exists(),
            'company' => $workspace ? ['workspace_id' => $workspace->id, 'role' => $member->role, 'status' => $workspace->status] : null];
    }

    private function token(User $u, string $device): array
    {
        // Only tokens issued for this client are rotated; other integrations are untouched.
        $u->tokens()->where('name', 'mobile:'.$device)->delete();
        $tokens = $u->tokens()->where('name', 'like', 'mobile:%')->orderByDesc('id')->get();
        foreach ($tokens->slice(19) as $old) {
            $old->delete();
        }
        $expires = now()->addDays(30);
        $token = $u->createToken('mobile:'.$device, ['mobile:v1'], $expires);

        return ['token_type' => 'Bearer', 'access_token' => $token->plainTextToken,
            'expires_at' => $expires->utc()->toIso8601String(), 'user' => self::identity($u)];
    }

    public function register(Request $r)
    {
        $r->merge(['name' => trim((string) $r->input('name')), 'email' => strtolower(trim((string) $r->input('email')))]);
        $d = $r->validate([
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email', new NonDisposableEmail()],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
            'device_name' => ['nullable', 'string', 'max:80'],
            'account_type' => ['prohibited'], 'phone' => ['prohibited'], 'phone_country_code' => ['prohibited'],
            'company_name' => ['prohibited'], 'country_code' => ['prohibited'], 'city' => ['prohibited'],
            'customer_id' => ['prohibited'], 'role' => ['prohibited'], 'email_verified_at' => ['prohibited'],
        ]);
        $u = User::create([
            'name' => $d['name'], 'email' => $d['email'], 'password' => Hash::make($d['password']),
            'phone_verification_required' => true,
        ]);
        try {
            $u->notify(new MobileVerifyEmail);
        } catch (\Throwable $e) {
            report($e);
        }

        return response()->json([
            'message' => 'Verify your email address before signing in. Phone and company setup happen after sign-in.',
            'data' => ['email' => $u->email, 'verification_required' => true, 'email_verification_required' => true],
        ], 202);
    }

    public function login(Request $r)
    {
        $d = $r->validate(['email' => ['required', 'email', 'max:255'], 'password' => ['required', 'string', 'max:128'],
            'device_name' => ['required', 'string', 'max:80']]);
        $email = strtolower(trim($d['email']));
        $u = User::where('email', $email)->first();
        if (! $u || ! Hash::check($d['password'], $u->password) || ! $u->hasVerifiedEmail() || $u->blocked_at || NonDisposableEmail::isDisposable($email)) {
            throw ValidationException::withMessages(['email' => ['Invalid credentials.']]);
        }
        if ($u->hasRole(['admin', 'super-admin'])) {
            return response()->json(['message' => 'Platform administrator accounts use the web dashboard.', 'error_code' => 'AUTH_ADMIN_WEB_ONLY'], 403);
        }
        $u->forceFill(['last_login_ip' => $r->ip(), 'last_login_at' => now()])->save();

        return response()->json(['data' => $this->token($u, $d['device_name'])]);
    }

    public function changePassword(Request $r)
    {
        $d = $r->validate(['current_password' => ['required', 'string', 'max:128'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed', 'different:current_password']]);
        DB::transaction(function () use ($r, $d) {
            $user = User::whereKey($r->user()->id)->lockForUpdate()->firstOrFail();
            if (! Hash::check($d['current_password'], $user->password)) {
                throw ValidationException::withMessages(['current_password' => ['The current password is incorrect.']]);
            }
            $user->forceFill(['password' => Hash::make($d['password']), 'remember_token' => Str::random(60)])->save();
            // Preserve the authenticated phone and revoke every other API token.
            $user->tokens()->where('id', '!=', $r->user()->currentAccessToken()->id)->delete();
        });

        return response()->noContent();
    }

    public function me(Request $r)
    {
        return response()->json(['data' => self::identity($r->user())]);
    }

    public function workspace(Request $r, CompanyMarketplaceService $market)
    {
        $w = $market->actor($r->user());
        $plan = app(CompanyWorkspaceService::class)->plan($w);

        return response()->json(['data' => ['id' => $w->id, 'status' => $w->status,
            'plan' => array_intersect_key($plan, array_flip(['code', 'name_ar', 'name_en', 'seats', 'active_opportunities', 'monthly_applications', 'expires_at'])),
            'free_launch' => (bool) config('platform_billing.free_launch', true)]]);
    }

    public function logout(Request $r)
    {
        $r->user()->currentAccessToken()->delete();

        return response()->noContent();
    }

    public function devices(Request $r)
    {
        return response()->json(['data' => $r->user()->tokens()->where('name', 'like', 'mobile:%')->get()
            ->map(fn ($t) => ['id' => $t->id, 'device_name' => substr($t->name, 7), 'last_used_at' => $t->last_used_at?->toIso8601String(),
                'expires_at' => $t->expires_at?->toIso8601String(), 'current' => $t->id === $r->user()->currentAccessToken()->id])]);
    }

    public function revoke(Request $r, string $device)
    {
        $r->user()->tokens()->where('name', 'like', 'mobile:%')->whereKey($device)->firstOrFail()->delete();

        return response()->noContent();
    }

    public function resend(Request $r)
    {
        $d = $r->validate(['email' => ['required', 'string', 'email', 'max:255']]);
        $u = User::where('email', strtolower(trim($d['email'])))->first();

        if ($u && ! $u->hasVerifiedEmail()) {
            try {
                $u->notify(new MobileVerifyEmail);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'message' => 'If the account exists and is not verified, a verification email will be sent.',
        ]);
    }

    public function verify(Request $r, string $id, string $hash)
    {
        $u = User::findOrFail($id);
        abort_unless(hash_equals(sha1($u->getEmailForVerification()), $hash), 403);
        if (! $u->hasVerifiedEmail()) {
            $u->markEmailAsVerified();
            event(new Verified($u));
        }

        return response()->json(['data' => ['email_verified' => true]]);
    }

    public function forgot(Request $r)
    {
        $d = $r->validate(['email' => ['required', 'email', 'max:255']]);
        Password::sendResetLink(['email' => strtolower(trim($d['email']))]);

        return response()->json(['message' => 'If the account exists, password reset instructions will be sent.']);
    }

    public function reset(Request $r)
    {
        $r->merge(['email' => strtolower(trim((string) $r->input('email')))]);
        $d = $r->validate(['email' => ['required', 'email'], 'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed']]);
        $status = Password::reset($d, function (User $u, string $password) {
            DB::transaction(function () use ($u, $password) {
                $u->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
                $u->tokens()->delete();
            });
            event(new PasswordReset($u));
        });
        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages(['email' => ['Invalid or expired reset request.']]);
        }

        return response()->json(['message' => 'Password reset. Sign in again.']);
    }
}
