<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShipmentController extends Controller
{
    /**
     * Display all shipments for the authenticated customer.
     */
    public function index(Request $request): View
    {
        $query = Shipment::with([
            'originPort',
            'destinationPort',
            'vessel',
        ])->where(
            'customer_id',
            $request->user()->customer_id
        );

        // Optional status filter
        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')->toString()
            );
        }

        $shipments = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('shipments.index', [
            'shipments' => $shipments,
        ]);
    }

    /**
     * Display a single shipment.
     */
    public function show(
        Request $request,
        Shipment $shipment
    ): View {
        abort_unless(
            $shipment->customer_id === $request->user()->customer_id,
            403
        );

        $shipment->load([
            'originPort',
            'destinationPort',
            'vessel',
            'containers',
            'documents',
            'events',
        ]);

        return view('shipments.show', [
            'shipment' => $shipment,
        ]);
    }

    /**
     * Display shipment tracking.
     */
    public function tracking(
        Request $request,
        Shipment $shipment
    ): View {
        abort_unless(
            $shipment->customer_id === $request->user()->customer_id,
            403
        );

        $shipment->load([
            'vessel',
        ]);

        $events = $shipment
            ->events()
            ->latest('event_date')
            ->get();

        return view('shipments.tracking', [
            'shipment' => $shipment,
            'events' => $events,
        ]);
    }

    /**
     * Dashboard shipment statistics.
     */
    public function dashboard(Request $request): View
    {
        $query = Shipment::where(
            'customer_id',
            $request->user()->customer_id
        );

        $stats = [
            'total_shipments' => (clone $query)->count(),

            'in_transit' => (clone $query)
                ->where('status', 'in_transit')
                ->count(),

            'pending' => (clone $query)
                ->where('status', 'pending')
                ->count(),

            'delivered' => (clone $query)
                ->where('status', 'delivered')
                ->count(),
        ];

        return view('dashboard', [
            'stats' => $stats,
        ]);
    }
}
