<?php

namespace App\Http\Controllers;

use App\Models\FreightRate;
use App\Services\Freight\FreightRateBookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class FreightRateSelectionController extends Controller
{
    public function store(Request $request, FreightRate $rate, FreightRateBookingService $bookings): RedirectResponse
    {
        $data = $request->validate([
            'request_key' => ['required', 'uuid'],
            'quantity' => ['required', 'integer', 'between:1,10000'],
            'preferred_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.now('Asia/Dubai')->toDateString()],
            'cargo_type' => ['nullable', 'string', 'max:255'],
        ]);

        $result = $bookings->select($request->user(), $rate, $data);

        return redirect()->route('quotations.show', $result['quotation'])
            ->with('success', $result['created']
                ? __('freight_selection.submitted')
                : __('freight_selection.already_submitted'));
    }
}
