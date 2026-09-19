<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Customer;
use App\Models\Quotation;
use App\Models\Shipment;
use App\Models\Vessel;
use App\Models\User;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    public function index(): View
    {
        $stats = [
            'customers' => Customer::count(),
            'users' => User::count(),
            'shipments' => Shipment::count(),
            'quotations' => Quotation::count(),
            'carriers' => Carrier::count(),
            'vessels' => Vessel::count(),

            'pending_shipments' => Shipment::where(
                'status',
                'pending'
            )->count(),

            'in_transit_shipments' => Shipment::where(
                'status',
                'in_transit'
            )->count(),

            'delivered_shipments' => Shipment::where(
                'status',
                'delivered'
            )->count(),
        ];

        $recentShipments = Shipment::query()
            ->with([
                'customer',
                'originPort',
                'destinationPort',
                'vessel',
                'carrier',
            ])
            ->latest()
            ->take(10)
            ->get();

        $recentQuotations = Quotation::query()
            ->with([
                'customer',
                'originPort',
                'destinationPort',
            ])
            ->latest()
            ->take(10)
            ->get();

        return view('admin.reports.index', [
            'stats' => $stats,
            'recentShipments' => $recentShipments,
            'recentQuotations' => $recentQuotations,
        ]);
    }
}
