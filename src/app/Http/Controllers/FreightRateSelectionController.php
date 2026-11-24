<?php

namespace App\Http\Controllers;

use App\Models\FreightRate;
use App\Services\Freight\FreightRateBookingService;
use App\Services\FreightQuotationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class FreightRateSelectionController extends Controller
{
    public function store(Request $request, FreightRate $rate, FreightRateBookingService $bookings, FreightQuotationService $quotations): RedirectResponse
    {
        $data = $request->validate([
            'request_key' => ['required', 'uuid'],
            'quantity' => ['required', 'integer', 'between:1,10000'],
            'preferred_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.now('Asia/Dubai')->toDateString()],
            'cargo_type' => ['nullable', 'string', 'max:255'],
        ]);

        $user = $request->user();
        if ($user && $user->hasVerifiedEmail() && $user->hasCompletedPhoneSetup() && $user->customer_id) {
            $quotations->customer($user);
            $result = $bookings->select($user, $rate, $data);

            return $this->success($result);
        }

        $token = (string) Str::uuid();
        $drafts = $request->session()->get('pending_freight_rate_drafts', []);
        $drafts[$token] = ['rate_id' => $rate->id, 'data' => $data, 'created_at' => now()->timestamp];
        $request->session()->put('pending_freight_rate_drafts', $drafts);
        $url = route('freight.rates.resume', ['draft' => $token]);
        $request->session()->put('url.intended', $url);

        if (! $user) {
            return redirect()->route('login');
        }
        if (! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }
        if (! $user->hasCompletedPhoneSetup()) {
            return redirect()->route('phone.verification.notice');
        }
        if (! $user->customer_id) {
            return redirect()->route('company.onboarding.show');
        }

        return redirect()->to($url);
    }

    public function resume(Request $request, string $draft): \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
    {
        $pending = $this->pending($request, $draft);
        $user = $request->user();
        $url = route('freight.rates.resume', ['draft' => $draft]);
        if (! $user->hasCompletedPhoneSetup()) {
            $request->session()->put('url.intended', $url);
            return redirect()->route('phone.verification.notice');
        }
        if (! $user->customer_id) {
            $request->session()->put('url.intended', $url);
            return redirect()->route('company.onboarding.show');
        }
        app(FreightQuotationService::class)->customer($user);

        $rate = FreightRate::with(['carrier', 'originPort', 'destinationPort'])->findOrFail($pending['rate_id']);
        return response()->view('freight.rate-selection-resume', compact('pending', 'rate'))
            ->header('Cache-Control', 'private, no-store');
    }

    public function complete(Request $request, string $draft, FreightRateBookingService $bookings, FreightQuotationService $quotations): RedirectResponse
    {
        $pending = $this->pending($request, $draft);
        $user = $request->user();
        $url = route('freight.rates.resume', ['draft' => $draft]);
        if (! $user->hasCompletedPhoneSetup()) {
            $request->session()->put('url.intended', $url);
            return redirect()->route('phone.verification.notice');
        }
        if (! $user->customer_id) {
            $request->session()->put('url.intended', $url);
            return redirect()->route('company.onboarding.show');
        }
        $quotations->customer($user);
        $rate = FreightRate::findOrFail($pending['rate_id']);
        $result = $bookings->select($user, $rate, $pending['data']);
        $this->forget($request, $draft);

        return $this->success($result);
    }

    private function success(array $result): RedirectResponse
    {
        return redirect()->route('quotations.show', $result['quotation'])
            ->with('success', $result['created']
                ? __('freight_selection.submitted')
                : __('freight_selection.already_submitted'));
    }

    private function pending(Request $request, string $token): array
    {
        $draft = $request->session()->get('pending_freight_rate_drafts.'.$token);
        abort_unless(is_array($draft) && isset($draft['rate_id'], $draft['data'], $draft['created_at']), 404);
        $userId = (int) $request->user()->id;
        if (! isset($draft['bound_user_id'])) {
            $draft['bound_user_id'] = $userId;
            $drafts = $request->session()->get('pending_freight_rate_drafts', []);
            $drafts[$token] = $draft;
            $request->session()->put('pending_freight_rate_drafts', $drafts);
        }
        abort_unless((int) $draft['bound_user_id'] === $userId, 403);
        if ((int) $draft['created_at'] < now()->subDay()->timestamp) {
            $this->forget($request, $token);
            abort(410, 'This freight rate selection draft has expired.');
        }
        return $draft;
    }

    private function forget(Request $request, string $token): void
    {
        $drafts = $request->session()->get('pending_freight_rate_drafts', []);
        unset($drafts[$token]);
        $request->session()->put('pending_freight_rate_drafts', $drafts);
    }
}
