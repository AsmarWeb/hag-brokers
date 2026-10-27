<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminShipmentController extends Controller
{
    /**
     * Display all shipments.
     */
    public function index(Request $request): View
    {
        $query = Shipment::with([
            'originPort',
            'destinationPort',
            'vessel',
        ]);

        if ($request->filled('status')) {
            $query->where(
                'status',
                $request->string('status')->toString()
            );
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();

            $query->where(function ($q) use ($search) {
                $q->where('booking_no', 'like', "%{$search}%")
                    ->orWhere('shipment_no', 'like', "%{$search}%");
            });
        }

        $shipments = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.shipments.index', [
            'shipments' => $shipments,
        ]);
    }

    /**
     * Display a shipment.
     */
    public function show(Shipment $shipment): View
    {
        $shipment->load([
            'originPort',
            'destinationPort',
            'vessel',
            'containers',
            'documents',
            'events',
        ]);

        return view('admin.shipments.show', [
            'shipment' => $shipment,
        ]);
    }
}
