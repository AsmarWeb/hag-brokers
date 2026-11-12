<?php

namespace App\Http\Controllers;

use App\Models\CompanyMember;
use App\Models\Shipment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = $user->customer_id
            ? Shipment::where('customer_id', $user->customer_id)
            : Shipment::whereRaw('1 = 0');

        $stats = [
            'total_shipments' => (clone $query)->count(),
            'in_transit' => (clone $query)->where('status', 'in_transit')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'delivered' => (clone $query)->where('status', 'delivered')->count(),
        ];

        return view('dashboard', [
            'stats' => $stats,
            'hasCompanyWorkspace' => CompanyMember::where('user_id', $user->id)->exists(),
            'phoneSetupCompleted' => $user->hasCompletedPhoneSetup(),
            'hasCarrierFleet' => $user->hasVerifiedEmail()
                && $user->carrierPartnership()->whereHas('carrier', fn ($query) => $query->where('is_active', true))->exists(),
        ]);
    }
}
