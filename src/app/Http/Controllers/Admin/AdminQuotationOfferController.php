<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Carrier;
use App\Models\Quotation;
use App\Models\QuotationOffer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminQuotationOfferController extends Controller
{
    public function store(Request $request, Quotation $quotation): RedirectResponse
    {
        $data = $request->validate([
            'carrier_id' => ['required', 'integer', 'exists:carriers,id'],
            'carrier_amount' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'customer_amount' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'currency' => ['required', 'string', 'size:3', 'regex:/^[A-Za-z]{3}$/'],
            'departure_date' => ['nullable', 'date'],
            'eta' => ['nullable', 'date', 'after_or_equal:departure_date'],
            'transit_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ]);

        $carrier = Carrier::query()
            ->whereKey($data['carrier_id'])
            ->where('is_active', true)
            ->first();

        if (! $carrier) {
            throw ValidationException::withMessages([
                'carrier_id' => 'Choose an active carrier.',
            ]);
        }

        if ((float) $data['customer_amount'] < (float) $data['carrier_amount']) {
            throw ValidationException::withMessages([
                'customer_amount' => 'Customer price must not be lower than carrier cost.',
            ]);
        }

        $data['currency'] = strtoupper($data['currency']);
        $data['status'] = 'pending';

        DB::transaction(function () use ($quotation, $request, $data) {
            $offer = $quotation->offers()->create($data);

            DB::table('quotation_history')->insert([
                'quotation_id' => $quotation->id,
                'user_id' => $request->user()->id,
                'event' => 'offer_created',
                'old_value' => null,
                'new_value' => (string) $offer->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.quotations.show', $quotation)
            ->with('success', 'Carrier offer saved successfully.');
    }
}
