<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\Shipment;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_shipments' => Shipment::count(),

            'in_transit' => Shipment::where(
                'status',
                'in_transit'
            )->count(),

            'delivered' => Shipment::where(
                'status',
                'delivered'
            )->count(),

            'pending_shipments' => Shipment::where(
                'status',
                'pending'
            )->count(),

            'total_quotations' => Quotation::count(),

            'pending_quotations' => Quotation::where(
                'status',
                'pending'
            )->count(),
        ];

        $recentShipments = Shipment::with([
            'originPort',
            'destinationPort',
            'vessel',
        ])
            ->latest()
            ->limit(10)
            ->get();

        $recentQuotations = Quotation::latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentShipments' => $recentShipments,
            'recentQuotations' => $recentQuotations,
        ]);
    }
}
