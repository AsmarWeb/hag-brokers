<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\CarrierInvitation;
use App\Models\CarrierVesselRequest;
use App\Models\User;
use App\Rules\NonDisposableEmail;
use App\Services\Auth\PhoneNumber;
use App\Services\Auth\PhoneVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

final class CarrierPartnerController extends Controller
{
    public function invite(Request $request, Carrier $carrier)
    {
        abort_unless($request->user()->hasVerifiedEmail(), 403);

        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $data = $request->validate(['email' => ['required', 'email', 'max:255', new NonDisposableEmail()]]);
        $token = Str::random(64);

        DB::transaction(function () use ($carrier, $data, $token) {
            $active = Carrier::lockForUpdate()->findOrFail($carrier->id);
            if (! $active->is_active) {
                throw ValidationException::withMessages(['email' => __('carrier_partner.inactive')]);
            }

            CarrierInvitation::create([
                'carrier_id' => $active->id,
                'email' => Str::lower($data['email']),
                'token_hash' => hash('sha256', $token),
                'expires_at' => now()->addDays(7),
            ]);
        }, 3);

        return redirect()->route('admin.carriers.show', $carrier)
            ->with('carrier_invitation_url', route('carrier.onboarding.show', ['token' => $token]));
    }

    public function revoke(Request $request, Carrier $carrier, CarrierInvitation $invitation)
    {
        abort_unless($request->user()->hasVerifiedEmail(), 403);

        DB::transaction(function () use ($carrier, $invitation) {
            Carrier::lockForUpdate()->findOrFail($carrier->id);
            $target = CarrierInvitation::where('carrier_id', $carrier->id)
                ->lockForUpdate()->findOrFail($invitation->id);

            $status = $target->status();
            if ($status === 'accepted') {
                CarrierInvitation::where('carrier_id', $carrier->id)->where('email', $target->email)
                    ->whereNull('accepted_at')->whereNull('revoked_at')->where('expires_at', '>', now())
                    ->update(['revoked_at' => now()]);
            }
            if (in_array($status, ['pending', 'accepted'], true)) {
                $target->forceFill(['revoked_at' => now(), 'user_id' => null])->save();
            }
        }, 3);

        return redirect()->route('admin.carriers.show', $carrier)
            ->with('carrier_invitation_status', __('carrier_partner.revoked_notice'));
    }

    public function onboarding(Request $request, string $token)
    {
        $invitation = $this->pending($token);
        abort_unless($invitation->carrier?->is_active, 404);

        if ($request->user()) {
            return redirect()->route('carrier.invitation.show', ['token' => $token])
                ->header('Referrer-Policy', 'no-referrer');
        }

        return response()->view('carrier-partner.onboarding', compact('invitation', 'token'))
            ->header('Cache-Control', 'no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function register(Request $request, string $token)
    {
        $request->merge(['phone' => PhoneNumber::normalize($request->input('phone'))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\\+[1-9][0-9]{7,14}$/D'],
            'password' => ['required', 'confirmed', Password::defaults()->min(8)->max(128)],
        ]);

        $user = DB::transaction(function () use ($token, $data) {
            $pending = $this->pending($token);
            abort_unless($pending->carrier()->lockForUpdate()->first()?->is_active, 404);
            $invitation = $this->pending($token, true);
            if (User::whereRaw('LOWER(email) = ?', [$invitation->email])->exists()) {
                throw ValidationException::withMessages(['name' => __('carrier_partner.existing_account')]);
            }

            return User::create([
                'name' => $data['name'],
                'email' => $invitation->email,
                'phone' => $data['phone'],
                'phone_verification_required' => true,
                'password' => Hash::make($data['password']),
            ]);
        }, 3);

        $request->session()->regenerate();
        $request->session()->put('url.intended', route('carrier.invitation.show', ['token' => $token]));
        try {
            $user->sendEmailVerificationNotification();
        } catch (\\Throwable $exception) {
            report($exception);
        }

        app(PhoneVerificationService::class)->send($user);

        return redirect()->route('login')->with('status', __('auth.verification_email_sent'));
    }

    public function show(Request $request, string $token)
    {
        $invitation = $this->pending($token);
        $this->authorizeRecipient($request->user(), $invitation);
        abort_unless($invitation->carrier?->is_active, 404);

        return response()->view('carrier-partner.invitation', compact('invitation', 'token'))
            ->header('Cache-Control', 'no-store')
            ->header('Referrer-Policy', 'no-referrer');
    }

    public function accept(Request $request, string $token)
    {
        DB::transaction(function () use ($request, $token) {
            $user = User::lockForUpdate()->findOrFail($request->user()->id);
            $pending = $this->pending($token);
            $this->authorizeRecipient($user, $pending);
            abort_unless($pending->carrier()->lockForUpdate()->first()?->is_active, 404);
            $invitation = $this->pending($token, true);
            abort_if($user->carrierPartnership()->exists(), 409);

            $invitation->forceFill(['user_id' => $user->id, 'accepted_at' => now()])->save();
        }, 3);

        return redirect()->route('carrier.fleet');
    }

    public function fleet(Request $request)
    {
        $partnership = $this->partnership($request->user());

        $vesselRequests = CarrierVesselRequest::where('carrier_id', $partnership->carrier_id);

        return response()->view('carrier-partner.fleet', [
            'carrier' => $partnership->carrier,
            'vessels' => $partnership->carrier->vessels()->orderBy('name')->orderBy('id')->paginate(20)->withQueryString(),
            'vesselRequests' => (clone $vesselRequests)->latest('id')->paginate(10, pageName: 'requests')->withQueryString(),
            'pendingRequests' => $vesselRequests->where('status', 'pending')->count(),
        ])->header('Cache-Control', 'no-store');
    }

    public function updateProfile(Request $request)
    {
        $this->partnership($request->user());
        $request->merge(['phone' => PhoneNumber::normalize($request->input('phone'))]);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'regex:/^\\+[1-9][0-9]{7,14}$/D'],
        ]);
        $user = $request->user();
        $phoneChanged = $user->phone !== $data['phone'];
        $user->name = $data['name'];
        $user->phone = $data['phone'];
        if ($phoneChanged) {
            $user->phone_verified_at = null;
            $user->phone_verification_required = true;
            $user->tokens()->delete();
        }
        $user->save();
        if ($phoneChanged) {
            app(PhoneVerificationService::class)->send($user);
        }

        return back()->with('carrier_status', __('carrier_partner.profile_saved'));
    }

    public function issueToken(Request $request)
    {
        $request->validate(['password' => ['required', 'current_password']]);
        $token = DB::transaction(function () use ($request) {
            $user = User::lockForUpdate()->findOrFail($request->user()->id);
            $partnership = $this->partnership($user);
            $name = 'carrier-partner:'.$partnership->id;
            $user->tokens()->where('name', $name)->delete();

            return $user->createToken($name, ['carrier:fleet:'.$partnership->id], now()->addDays(30))->plainTextToken;
        }, 3);

        return back()->with('carrier_api_token', $token);
    }

    public function revokeToken(Request $request)
    {
        $request->validate(['password' => ['required', 'current_password']]);
        DB::transaction(function () use ($request) {
            $user = User::lockForUpdate()->findOrFail($request->user()->id);
            $partnership = $this->partnership($user);
            $user->tokens()->where('name', 'carrier-partner:'.$partnership->id)->delete();
        }, 3);

        return back()->with('carrier_status', __('carrier_partner.token_revoked'));
    }

    public function apiFleet(Request $request)
    {
        $user = $request->user();
        $partnership = $this->partnership($user);
        $token = $user->currentAccessToken();
        abort_unless($token instanceof PersonalAccessToken
            && in_array('carrier:fleet:'.$partnership->id, $token->abilities, true), 403);

        $vessels = $partnership->carrier->vessels()->orderBy('name')->orderBy('id')
            ->paginate(20, ['id', 'name', 'imo']);

        return response()->json([
            'data' => $vessels->items(),
            'meta' => ['current_page' => $vessels->currentPage(), 'last_page' => $vessels->lastPage()],
        ])->header('Cache-Control', 'no-store');
    }

    private function partnership(User $user): CarrierInvitation
    {
        abort_unless($user->hasVerifiedEmail(), 403);
        $partnership = $user->carrierPartnership()->with('carrier')->first();
        abort_unless($partnership?->carrier?->is_active, 403);

        return $partnership;
    }

    private function pending(string $token, bool $lock = false): CarrierInvitation
    {
        return CarrierInvitation::where('token_hash', hash('sha256', $token))
            ->whereNull('accepted_at')->whereNull('revoked_at')->where('expires_at', '>', now())
            ->when($lock, fn ($query) => $query->lockForUpdate())
            ->firstOrFail();
    }

    private function authorizeRecipient(User $user, CarrierInvitation $invitation): void
    {
        abort_unless($user->hasVerifiedEmail() && Str::lower($user->email) === $invitation->email, 403);
    }
}
