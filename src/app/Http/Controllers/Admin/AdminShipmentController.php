<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Port;
use App\Models\Quotation;
use App\Models\Shipment;
use App\Models\Vessel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminShipmentController extends Controller
{
    /**
     * Display all shipments.
     */
    public function index(Request $request): View
    {
        $query = Shipment::query()
            ->with([
                'customer',
                'originPort',
                'destinationPort',
                'carrier',
                'vessel',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Status filter
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')->toString()
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('shipment_no', 'like', "%{$search}%")
                    ->orWhere('booking_no', 'like', "%{$search}%")

                    ->orWhereHas('customer', function ($customer) use ($search) {
                        $customer
                            ->where('company_name', 'like', "%{$search}%")
                            ->orWhere(
                                'contact_person',
                                'like',
                                "%{$search}%"
                            );
                    })

                    ->orWhereHas('carrier', function ($carrier) use ($search) {
                        $carrier->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );
                    })

                    ->orWhereHas('vessel', function ($vessel) use ($search) {
                        $vessel->where(
                            'name',
                            'like',
                            "%{$search}%"
                        );
                    });
            });
        }

        $shipments = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view(
            'admin.shipments.index',
            compact('shipments')
        );
    }

    /**
     * Show create shipment form.
     */
    public function create(): View
    {
        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('company_name')
            ->get();

        $quotations = Quotation::query()
            ->with([
                'customer',
                'originPort',
                'destinationPort',
            ])
            ->latest()
            ->get();

        $carriers = Carrier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $vessels = Vessel::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Active Ports
        |--------------------------------------------------------------------------
        */

        $ports = Port::query()
            ->where('is_active', true)
            ->orderBy('country')
            ->orderBy('name')
            ->get();

        return view(
            'admin.shipments.create',
            compact(
                'customers',
                'quotations',
                'carriers',
                'vessels',
                'ports'
            )
        );
    }

    /**
     * Store a shipment.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'customer_id' => [
                'required',
                'exists:customers,id',
            ],

            'quotation_id' => [
                'nullable',
                'exists:quotations,id',
            ],

            'carrier_id' => [
                'nullable',
                'exists:carriers,id',
            ],

            'vessel_id' => [
                'nullable',
                'exists:vessels,id',
            ],

            'origin_port_id' => [
                'required',
                'exists:ports,id',
            ],

            'destination_port_id' => [
                'required',
                'exists:ports,id',
                'different:origin_port_id',
            ],

            'booking_no' => [
                'nullable',
                'string',
                'max:255',
                'unique:shipments,booking_no',
            ],

            'cargo_type' => [
                'nullable',
                'string',
                'max:255',
            ],

            'container_type' => [
                'nullable',
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

            'etd' => [
                'nullable',
                'date',
            ],

            'eta' => [
                'nullable',
                'date',
                'after_or_equal:etd',
            ],

            'status' => [
                'required',
                'string',
                'max:50',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Generate Shipment Number
        |--------------------------------------------------------------------------
        */

        $nextId = (Shipment::max('id') ?? 0) + 1;

        $validated['shipment_no'] =
            'HAG-' .
            now()->format('Y') .
            '-' .
            str_pad(
                $nextId,
                5,
                '0',
                STR_PAD_LEFT
            );

        Shipment::create($validated);

        return redirect()
            ->route('admin.shipments.index')
            ->with(
                'success',
                'Shipment created successfully.'
            );
    }

    /**
     * Display a shipment.
     */
    public function show(Shipment $shipment): View
    {
        $shipment->load([
            'customer',
            'quotation',
            'originPort',
            'destinationPort',
            'carrier',
            'vessel',
            'containers',
            'documents',
            'events.port',
        ]);

        return view(
            'admin.shipments.show',
            compact('shipment')
        );
    }

    /**
     * Show edit form.
     */
    public function edit(Shipment $shipment): View
    {
        $customers = Customer::query()
            ->where('is_active', true)
            ->orderBy('company_name')
            ->get();

        $quotations = Quotation::query()
            ->with([
                'customer',
                'originPort',
                'destinationPort',
            ])
            ->latest()
            ->get();

        $carriers = Carrier::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $vessels = Vessel::query()
            ->orderBy('name')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Active Ports
        |--------------------------------------------------------------------------
        */

        $ports = Port::query()
            ->where('is_active', true)
            ->orderBy('country')
            ->orderBy('name')
            ->get();

        return view(
            'admin.shipments.edit',
            compact(
                'shipment',
                'customers',
                'quotations',
                'carriers',
                'vessels',
                'ports'
            )
        );
    }

    /**
     * Update shipment.
     */
    public function update(
        Request $request,
        Shipment $shipment
    ): RedirectResponse {
        $validated = $request->validate([
            'customer_id' => [
                'required',
                'exists:customers,id',
            ],

            'quotation_id' => [
                'nullable',
                'exists:quotations,id',
            ],

            'carrier_id' => [
                'nullable',
                'exists:carriers,id',
            ],

            'vessel_id' => [
                'nullable',
                'exists:vessels,id',
            ],

            'origin_port_id' => [
                'required',
                'exists:ports,id',
            ],

            'destination_port_id' => [
                'required',
                'exists:ports,id',
                'different:origin_port_id',
            ],

            'booking_no' => [
                'nullable',
                'string',
                'max:255',
                'unique:shipments,booking_no,' . $shipment->id,
            ],

            'cargo_type' => [
                'nullable',
                'string',
                'max:255',
            ],

            'container_type' => [
                'nullable',
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

            'etd' => [
                'nullable',
                'date',
            ],

            'eta' => [
                'nullable',
                'date',
                'after_or_equal:etd',
            ],

            'actual_departure' => [
                'nullable',
                'date',
            ],

            'actual_arrival' => [
                'nullable',
                'date',
            ],

            'status' => [
                'required',
                'string',
                'max:50',
            ],
        ]);

        $shipment->update($validated);

        return redirect()
            ->route(
                'admin.shipments.show',
                $shipment
            )
            ->with(
                'success',
                'Shipment updated successfully.'
            );
    }

    /**
     * Delete shipment.
     */
    public function destroy(
        Shipment $shipment
    ): RedirectResponse {
        /*
        |--------------------------------------------------------------------------
        | Protect shipment with related operational data
        |--------------------------------------------------------------------------
        */

        if ($shipment->containers()->exists()) {
            return redirect()
                ->route('admin.shipments.index')
                ->with(
                    'error',
                    'This shipment cannot be deleted because it has containers.'
                );
        }

        if ($shipment->documents()->exists()) {
            return redirect()
                ->route('admin.shipments.index')
                ->with(
                    'error',
                    'This shipment cannot be deleted because it has documents.'
                );
        }

        if ($shipment->events()->exists()) {
            return redirect()
                ->route('admin.shipments.index')
                ->with(
                    'error',
                    'This shipment cannot be deleted because it has tracking events.'
                );
        }

        $shipment->delete();

        return redirect()
            ->route('admin.shipments.index')
            ->with(
                'success',
                'Shipment deleted successfully.'
            );
    }
}
