<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Port;
use App\Models\Quotation;
use App\Services\FreightQuotationService;
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

        return view('quotations.create', ['ports' => $ports]);
    }

    /**
     * Store a new quotation request.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(FreightQuotationService::rules());

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
