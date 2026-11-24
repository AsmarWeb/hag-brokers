<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Port;
use App\Models\Quotation;
use App\Services\FreightQuotationService;
use App\Services\Freight\SailingCatalog;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class QuotationController extends Controller
{
    /**
     * Display the customer's quotations.
     */
    public function index(Request $request): View
    {
        app(FreightQuotationService::class)->customer($request->user());
        $quotations = Quotation::where(
            'customer_id',
            $request->user()->customer_id
        )
            ->with('freightRateBooking')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('quotations.index', [
            'quotations' => $quotations,
        ]);
    }

    /** Public form: guests may prepare a freight quotation draft. */
    public function create(Request $request): View
    {
        $ports = Port::query()->where('is_active', true)->orderBy('name')->get();
        $sailing = null;
        $selection = [];
        if ($request->filled('sailing')) {
            $selection = $request->validate([
                'sailing' => ['required', 'integer', 'min:1'],
                'container_type' => ['required', 'string'],
                'quantity' => ['required', 'integer', 'between:1,10000'],
                'ready_on' => ['nullable', 'date_format:Y-m-d'],
            ]);
            $sailing = app(SailingCatalog::class)->available()->findOrFail($selection['sailing']);
            abort_unless(in_array($selection['container_type'], $sailing->container_types, true), 422);
        }

        return view('quotations.create', [
            'ports' => $ports,
            'sailing' => $sailing,
            'selection' => $selection,
            'requestKey' => (string) Str::uuid(),
        ]);
    }

    /**
     * Validate and either submit for an already-ready customer or keep a
     * 24-hour session draft until sign-in, verification and company setup.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(FreightQuotationService::rules() + [
            'request_key' => ['required', 'uuid'],
        ]);
        $requestKey = $data['request_key'];
        unset($data['request_key']);

        $user = $request->user();
        $service = app(FreightQuotationService::class);
        if ($user && $user->hasVerifiedEmail() && $user->hasCompletedPhoneSetup() && $user->customer_id) {
            // Existing legacy customers remain supported; workspace members must
            // still pass the service's owner/manager authorization checks.
            $service->customer($user);
            [$quotation] = $service->submit($user, $data, $requestKey);

            return redirect()->route('quotations.show', $quotation)->with('success', __('sailings.request_sent'));
        }

        $token = (string) Str::uuid();
        $drafts = $request->session()->get('pending_freight_quotation_drafts', []);
        $drafts[$token] = ['data' => $data, 'request_key' => $requestKey, 'created_at' => now()->timestamp];
        $request->session()->put('pending_freight_quotation_drafts', $drafts);

        return $this->redirectToQuotationDraft($request, $token);
    }

    public function resume(Request $request, string $draft): View|RedirectResponse
    {
        $pending = $this->pendingQuotationDraft($request, $draft);
        $user = $request->user();
        $url = route('quotations.resume', ['draft' => $draft]);
        if (! $user->hasCompletedPhoneSetup()) {
            $request->session()->put('url.intended', $url);
            return redirect()->route('phone.verification.notice');
        }
        if (! $user->customer_id) {
            $request->session()->put('url.intended', $url);
            return redirect()->route('company.onboarding.show');
        }

        app(FreightQuotationService::class)->customer($user);
        $data = $pending['data'];
        $origin = Port::find($data['origin_port_id']);
        $destination = Port::find($data['destination_port_id']);
        $sailing = isset($data['freight_sailing_id']) ? \App\Models\FreightSailing::find($data['freight_sailing_id']) : null;

        return view('quotations.resume', compact('pending', 'data', 'origin', 'destination', 'sailing'));
    }

    public function complete(Request $request, string $draft): RedirectResponse
    {
        $pending = $this->pendingQuotationDraft($request, $draft);
        $user = $request->user();
        $url = route('quotations.resume', ['draft' => $draft]);
        if (! $user->hasCompletedPhoneSetup()) {
            $request->session()->put('url.intended', $url);
            return redirect()->route('phone.verification.notice');
        }
        if (! $user->customer_id) {
            $request->session()->put('url.intended', $url);
            return redirect()->route('company.onboarding.show');
        }

        $service = app(FreightQuotationService::class);
        $service->customer($user);
        $data = \Illuminate\Support\Facades\Validator::make($pending['data'], FreightQuotationService::rules())->validate();
        [$quotation] = $service->submit($user, $data, $pending['request_key']);
        $this->forgetQuotationDraft($request, $draft);

        return redirect()->route('quotations.show', $quotation)->with('success', __('sailings.request_sent'));
    }

    private function redirectToQuotationDraft(Request $request, string $token): RedirectResponse
    {
        $url = route('quotations.resume', ['draft' => $token]);
        $request->session()->put('url.intended', $url);
        if (! $request->user()) {
            return redirect()->route('login');
        }
        if (! $request->user()->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }
        if (! $request->user()->hasCompletedPhoneSetup()) {
            return redirect()->route('phone.verification.notice');
        }
        if (! $request->user()->customer_id) {
            return redirect()->route('company.onboarding.show');
        }

        return redirect()->to($url);
    }

    private function pendingQuotationDraft(Request $request, string $token): array
    {
        $draft = $request->session()->get('pending_freight_quotation_drafts.'.$token);
        abort_unless(is_array($draft) && isset($draft['data'], $draft['request_key'], $draft['created_at']), 404);
        $userId = (int) $request->user()->id;
        if (! isset($draft['bound_user_id'])) {
            $draft['bound_user_id'] = $userId;
            $drafts = $request->session()->get('pending_freight_quotation_drafts', []);
            $drafts[$token] = $draft;
            $request->session()->put('pending_freight_quotation_drafts', $drafts);
        }
        abort_unless((int) $draft['bound_user_id'] === $userId, 403);
        if ((int) $draft['created_at'] < now()->subDay()->timestamp) {
            $this->forgetQuotationDraft($request, $token);
            abort(410, 'This freight quotation draft has expired.');
        }

        return $draft;
    }

    private function forgetQuotationDraft(Request $request, string $token): void
    {
        $drafts = $request->session()->get('pending_freight_quotation_drafts', []);
        unset($drafts[$token]);
        $request->session()->put('pending_freight_quotation_drafts', $drafts);
    }

        /**
     * Display a single quotation.
     */
    public function show(
        Request $request,
        Quotation $quotation
    ): View {
        app(FreightQuotationService::class)->customer($request->user());
        abort_unless(
            $quotation->customer_id ===
                $request->user()->customer_id,
            403
        );

        return view('quotations.show', [
            'quotation' => $quotation->load([
                'freightRateBooking.events' => fn ($query) => $query
                    ->reorder()
                    ->orderBy('created_at')
                    ->orderBy('id'),
            ]),
        ]);
    }
}
