<?php

namespace App\Http\Controllers;

use App\Models\FreightBookingOffer;
use App\Models\Quotation;
use App\Models\User;
use App\Services\Freight\SailingCatalog;
use App\Services\FreightQuotationService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class FreightBookingController extends Controller
{
    public function publish(Request $request, Quotation $quotation, SailingCatalog $catalog): RedirectResponse
    {
        $data = $request->validate([
            'expires_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'terms' => ['nullable', 'string', 'max:3000'],
            'revision' => ['nullable', 'uuid'],
        ]);

        DB::transaction(function () use ($request, $quotation, $catalog, $data) {
            $quote = Quotation::lockForUpdate()->findOrFail($quotation->id);
            $sailing = $this->currentSailing($quote, $catalog);
            $approved = $quote->offers()->whereKey($quote->approved_offer_id)->where('status', 'approved')->first();
            if (! $approved || (int) $approved->carrier_id !== (int) $sailing->carrier_id || $quote->approved_customer_amount <= 0) {
                $this->reject('approve_first');
            }
            $expiry = CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $data['expires_at'], 'Asia/Dubai')->utc();
            if ($expiry->lte(now()) || $expiry->gt($sailing->cargo_cutoff_at)
                || ($approved->expires_at && $expiry->gt($approved->expires_at))) {
                $this->reject('invalid_expiry');
            }

            $offer = $quote->freightBookingOffer()->first() ?? new FreightBookingOffer;
            if ($offer->accepted_at || $offer->revision !== ($data['revision'] ?? null)) {
                $this->reject('stale');
            }

            $quote->freightBookingOffer()->save($offer->forceFill([
                'revision' => (string) Str::uuid(),
                'amount' => $quote->approved_customer_amount,
                'currency' => $quote->approved_currency,
                'terms' => $data['terms'] ?? null,
                'expires_at' => $expiry,
            ]));
            $this->record($quote, $request->user(), 'freight_offer_published', $offer->revision);
        }, 3);

        return back()->with('success', __('freight_booking.published'));
    }

    public function accept(Request $request, Quotation $quotation, FreightQuotationService $freight, SailingCatalog $catalog): RedirectResponse
    {
        $data = $request->validate(['revision' => ['required', 'uuid']]);

        DB::transaction(function () use ($request, $quotation, $freight, $catalog, $data) {
            $customer = $freight->customer($request->user());
            $quote = Quotation::where('customer_id', $customer->id)->lockForUpdate()->findOrFail($quotation->id);
            $offer = $quote->freightBookingOffer()->firstOrFail();
            if ($offer->revision !== $data['revision']) {
                $this->reject('stale');
            }
            if ($offer->accepted_at) {
                return;
            }
            $this->currentSailing($quote, $catalog);
            if ($offer->expires_at->lte(now())) {
                $this->reject('expired');
            }

            $offer->forceFill(['accepted_at' => now()->utc()])->save();
            $this->record($quote, $request->user(), 'freight_offer_accepted', $offer->revision);
        }, 3);

        return back()->with('success', __('freight_booking.accepted'));
    }

    public function confirm(Request $request, Quotation $quotation, SailingCatalog $catalog): RedirectResponse
    {
        $data = $request->validate([
            'carrier_reference' => ['required', 'string', 'max:100'],
            'space_confirmed' => ['accepted'],
        ]);

        DB::transaction(function () use ($request, $quotation, $catalog, $data) {
            $quote = Quotation::lockForUpdate()->findOrFail($quotation->id);
            $offer = $quote->freightBookingOffer()->firstOrFail();
            if ($offer->confirmed_at && $offer->carrier_reference === $data['carrier_reference']) {
                return;
            }
            if (! $offer->accepted_at || $offer->confirmed_at) {
                $this->reject('stale');
            }
            $this->currentSailing($quote, $catalog);

            $offer->forceFill([
                'carrier_reference' => $data['carrier_reference'],
                'confirmed_at' => now()->utc(),
            ])->save();
            $this->record($quote, $request->user(), 'freight_booking_confirmed', $offer->revision);
        }, 3);

        return back()->with('success', __('freight_booking.confirmed'));
    }

    private function currentSailing(Quotation $quote, SailingCatalog $catalog): \App\Models\FreightSailing
    {
        $sailing = $catalog->available()->whereKey($quote->freight_sailing_id)->lockForUpdate()->first();
        // Compare decoded snapshots without depending on JSON key order in MySQL.
        if (! $sailing || $quote->status === 'rejected' || $catalog->snapshot($sailing) != $quote->sailing_snapshot) {
            $this->reject('sailing_changed');
        }

        return $sailing;
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['freight_booking' => __('freight_booking.'.$message)]);
    }

    private function record(Quotation $quote, User $actor, string $event, string $revision): void
    {
        DB::table('quotation_history')->insert([
            'quotation_id' => $quote->id,
            'user_id' => $actor->id,
            'event' => $event,
            'new_value' => $revision,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
