<?php

namespace App\Http\Controllers;

use App\Models\Port;
use App\Models\Quotation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuotationController extends Controller
{
    /**
     * Display the customer's quotations.
     */
    public function index(Request $request): View
    {
        $customerId = $request->user()->customer_id;

        abort_unless($customerId, 403);

        $quotations = Quotation::where('customer_id', $customerId)
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
    public function create(): View
    {
        $ports = Port::where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'country',
                'unlocode',
            ]);

        return view('quotations.create', [
            'ports' => $ports,
        ]);
    }

    /**
     * Store a new quotation request.
     */
    public function store(Request $request): RedirectResponse
    {
        $customerId = $request->user()->customer_id;

        abort_unless($customerId, 403);

        $data = $request->validate([
            'origin_port_id' => [
                'required',
                'exists:ports,id',
                'different:destination_port_id',
            ],

            'destination_port_id' => [
                'required',
                'exists:ports,id',
            ],

            'cargo_type' => [
                'required',
                'string',
                'max:255',
            ],

            'container_type' => [
                'required',
                'string',
                'max:255',
            ],

            'quantity' => [
                'required',
                'integer',
                'min:1',
            ],

            'weight' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'preferred_date' => [
                'nullable',
                'date',
            ],

            'notes' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ]);

        $data['customer_id'] = $customerId;

        /*
        |--------------------------------------------------------------------------
        | Generate quotation reference
        |--------------------------------------------------------------------------
        */

        $nextId = ((int) Quotation::max('id')) + 1;

        $data['reference_no'] =
            'QT-' .
            now()->format('Y') .
            '-' .
            str_pad(
                (string) $nextId,
                5,
                '0',
                STR_PAD_LEFT
            );

        /*
        |--------------------------------------------------------------------------
        | Default quotation status
        |--------------------------------------------------------------------------
        */

        $data['status'] = 'pending';

        $quotation = Quotation::create($data);

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