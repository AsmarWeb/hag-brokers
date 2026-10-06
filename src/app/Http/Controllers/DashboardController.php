<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
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
