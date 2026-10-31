<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Models\DealAgreement;
use App\Services\Revenue\DealApprovalService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AdminDealApprovalController extends Controller
{
    public function approve(
        Request $request,
        Deal $deal,
        DealAgreement $agreement,
        DealApprovalService $service
    ): RedirectResponse {
        $commission = $service->approve(
            $deal,
            $agreement,
            (int) $request->user()->getKey()
        );

        return back()->with(
            'success',
            'Agreement approved. Pending commission: '
            . $commission->amount . ' ' . $commission->currency
        );
    }
}
