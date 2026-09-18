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
    public function create(): View
    {
        $ports = Port::where('is_active', true)
            ->orderBy('country')
            ->orderBy('name')
            ->get();

        return view('quotations.create', [
            'ports' => $ports,
        ]);
    }

    /**
     * Store a new quotation request.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'origin_port_id' => [
                'required',
                'integer',
                'exists:ports,id',
                'different:destination_port_id',
            ],

            'destination_port_id' => [
                'required',
                'integer',
                'exists:ports,id',
                'different:origin_port_id',
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
        ]);

        $data['customer_id'] = $request->user()->customer_id;

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
        | Create quotation
        |--------------------------------------------------------------------------
        */

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

        $quotation->load([
            'originPort',
            'destinationPort',
            'offers.carrier',
            'offers.vessel',
        ]);

        return view('quotations.show', [
            'quotation' => $quotation,
        ]);
    }
}
