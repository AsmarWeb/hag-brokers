<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Deal;
use App\Services\Revenue\DealAgreementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class AdminDealAgreementController extends Controller
{
    public function store(
        Request $request,
        Deal $deal,
        DealAgreementService $service
    ): RedirectResponse {
        $data = $request->validate([
            'fee_kind' => [
                'required',
                Rule::in(['percent', 'fixed', 'spread']),
            ],
            'commission_bps' => [
                'nullable', 'integer', 'between:0,10000',
            ],
            'fixed_fee' => [
                'nullable', 'regex:/^(0|[1-9][0-9]{0,13})(\.[0-9]{1,2})?$/',
            ],
            'terms' => [
                'required', 'string', 'min:10', 'max:20000',
            ],
        ]);

        $agreement = $service->createDraft($deal, $data);

        return back()->with(
            'success',
            'Agreement draft created: version ' . $agreement->version
        );
    }
}
