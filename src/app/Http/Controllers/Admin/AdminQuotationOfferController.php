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
        if ($quotation->approved_offer_id !== null) {
            throw ValidationException::withMessages([
                'offer' => 'This quotation already has an approved offer.',
            ]);
        }

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
            $locked = Quotation::whereKey($quotation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->approved_offer_id !== null) {
                throw ValidationException::withMessages([
                    'offer' => 'This quotation already has an approved offer.',
                ]);
            }

            $offer = $locked->offers()->create($data);

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

    public function approve(
        Request $request,
        Quotation $quotation,
        QuotationOffer $offer
    ): RedirectResponse {
        DB::transaction(function () use ($request, $quotation, $offer) {
            $locked = Quotation::whereKey($quotation->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->approved_offer_id !== null) {
                throw ValidationException::withMessages([
                    'offer' => 'A carrier offer has already been approved.',
                ]);
            }

            $selected = QuotationOffer::query()
                ->whereKey($offer->id)
                ->where('quotation_id', $locked->id)
                ->lockForUpdate()
                ->first();

            if (! $selected) {
                abort(404);
            }

            if ($selected->status !== 'pending') {
                throw ValidationException::withMessages([
                    'offer' => 'Only pending offers can be approved.',
                ]);
            }

            if (
                $selected->expires_at !== null
                && $selected->expires_at->lte(now())
            ) {
                throw ValidationException::withMessages([
                    'offer' => 'This carrier offer has expired.',
                ]);
            }

            if (
                ! $selected->carrier()->where('is_active', true)->exists()
            ) {
                throw ValidationException::withMessages([
                    'offer' => 'The selected carrier is inactive.',
                ]);
            }

            if (
                $selected->carrier_amount === null
                || $selected->customer_amount === null
            ) {
                throw ValidationException::withMessages([
                    'offer' => 'Both cost and customer price are required.',
                ]);
            }

            // Convert two-decimal monetary values to integer minor units.
            $cost = self::toCents($selected->carrier_amount);
            $price = self::toCents($selected->customer_amount);

            if ($price < $cost) {
                throw ValidationException::withMessages([
                    'offer' => 'Customer price cannot be below carrier cost.',
                ]);
            }

            $currency = strtoupper((string) $selected->currency);

            if (! preg_match('/^[A-Z]{3}$/', $currency)) {
                throw ValidationException::withMessages([
                    'offer' => 'Invalid offer currency.',
                ]);
            }

            $selected->update(['status' => 'approved']);

            $locked->forceFill([
                'approved_offer_id' => $selected->id,
                'approved_carrier_amount' => number_format($cost / 100, 2, '.', ''),
                'approved_customer_amount' => number_format($price / 100, 2, '.', ''),
                'approved_margin' => number_format(($price - $cost) / 100, 2, '.', ''),
                'approved_currency' => $currency,
                'offer_approved_by' => $request->user()->id,
                'offer_approved_at' => now(),
            ])->save();

            DB::table('quotation_history')->insert([
                'quotation_id' => $locked->id,
                'user_id' => $request->user()->id,
                'event' => 'offer_approved',
                'old_value' => null,
                'new_value' => (string) $selected->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }, 3);

        return redirect()
            ->route('admin.quotations.show', $quotation)
            ->with('success', 'Carrier offer approved internally.');
    }

    private static function toCents(string $amount): int
    {
        if (! preg_match('/^\d{1,10}(\.\d{1,2})?$/', $amount)) {
            throw ValidationException::withMessages([
                'offer' => 'Invalid monetary amount.',
            ]);
        }

        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '0');

        return ((int) $whole * 100)
            + (int) str_pad($fraction, 2, '0');
    }

}
