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
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('quotations.index', [
            'quotations' => $quotations,
        ]);
    }

    /**
     * Show the quotation request form.
     */
    public function create(Request $request): View
    {
        app(FreightQuotationService::class)->customer($request->user());
        $ports = Port::query()->where('is_active', true)
            ->orderBy('name')
            ->get();

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

        return view('quotations.create', compact('ports', 'sailing', 'selection'));
    }

    /**
     * Store a new quotation request.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(FreightQuotationService::rules());

        if (isset($data['freight_sailing_id'])) {
            $validated = $request->validate(['request_key' => ['required', 'uuid']]);
            [$quotation] = app(FreightQuotationService::class)->submit($request->user(), $data, $validated['request_key']);

            return redirect()->route('quotations.show', $quotation)
                ->with('success', __('sailings.request_sent'));
        }

        unset($data['sailing_revision']);
        $customerId = $request->user()->customer_id;

        if (! $customerId || ! Customer::whereKey($customerId)->exists()) {
            return back()->withInput()->withErrors([
                'customer' => 'Please complete your customer profile before submitting a quotation.',
            ]);
        }

        app(FreightQuotationService::class)->customer($request->user());
        $data['customer_id'] = $customerId;

        $data['reference_no'] =
            'QT-'.
            now()->format('Y').
            '-'.
            strtoupper(str_replace('-', '', (string) Str::uuid()));

        $quotation = Quotation::create($data);

        try {
            NotificationService::admins(
                type: 'quotation.created',
                title: 'New quotation request',
                message: 'New quotation '.$quotation->reference_no.' requires review.',
                model: $quotation,
                data: [
                    'url' => route('admin.quotations.show', $quotation),
                ]
            );
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()
            ->route('quotations.show', $quotation)
            ->with(
                'success',
                'Quotation request submitted successfully.'
            );
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
            'quotation' => $quotation,
        ]);
    }
}
