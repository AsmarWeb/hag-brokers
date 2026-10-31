<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Services\Revenue\DealCreationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class AdminDealController extends Controller
{
    public function storeFromQuotation(
        Request $request,
        Quotation $quotation,
        DealCreationService $service
    ): RedirectResponse {
        $deal = $service->fromApprovedQuotation(
            $quotation,
            (int) $request->user()->getKey()
        );

        return redirect()
            ->route('admin.quotations.show', $quotation)
            ->with(
                'success',
                'Draft deal created successfully: ' . $deal->reference
            );
    }
}
