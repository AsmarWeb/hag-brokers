<?php

namespace App\Http\Controllers;

use App\Models\Carrier;
use App\Models\FreightRateBooking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

final class CarrierFreightRateBookingController extends Controller
{
    public function index(Request $request): View
    {
        $partnership = $this->partnership($request);
        $bookings = FreightRateBooking::query()
            ->where('carrier_id', $partnership->carrier_id)
            ->where('status', 'awaiting_carrier')
            ->with(['quotation.originPort', 'quotation.destinationPort'])
            ->latest('submitted_at')
            ->paginate(20);

        return view('carrier-partner.freight-bookings.index', [
            'carrier' => $partnership->carrier,
            'bookings' => $bookings,
        ]);
    }

    public function show(Request $request, int $booking): View
    {
        $partnership = $this->partnership($request);
        $booking = FreightRateBooking::query()
            ->where('carrier_id', $partnership->carrier_id)
            ->with(['quotation.originPort', 'quotation.destinationPort'])
            ->findOrFail($booking);

        return view('carrier-partner.freight-bookings.show', [
            'carrier' => $partnership->carrier,
            'booking' => $booking,
            'snapshot' => $booking->rate_snapshot ?? [],
        ]);
    }

    public function decision(Request $request, int $booking): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:accept,reject'],
            'carrier_reference' => ['required_if:decision,accept', 'nullable', 'string', 'min:2', 'max:120'],
            'note' => ['required_if:decision,reject', 'nullable', 'string', 'min:3', 'max:1500'],
        ]);

        $user = $request->user();

        $updated = DB::transaction(function () use ($user, $validated, $booking) {
            $partnership = $user->carrierPartnership()->lockForUpdate()->first();
            abort_unless($user->hasVerifiedEmail() && $partnership, 403);

            $carrier = Carrier::query()->lockForUpdate()->find($partnership->carrier_id);
            abort_unless($carrier?->is_active, 403);

            // Re-check the accepted, non-revoked partnership while holding its row lock.
            $currentPartnership = $user->carrierPartnership()
                ->whereKey($partnership->id)
                ->lockForUpdate()
                ->first();
            abort_unless($currentPartnership, 403);

            $record = FreightRateBooking::query()
                ->whereKey($booking)
                ->where('carrier_id', $carrier->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($record->status !== 'awaiting_carrier') {
                throw ValidationException::withMessages([
                    'decision' => [__('carrier_freight_bookings.already_processed')],
                ]);
            }

            $from = $record->status;
            if ($validated['decision'] === 'accept') {
                $record->forceFill([
                    'status' => 'carrier_confirmed',
                    'carrier_reference' => trim($validated['carrier_reference']),
                    'confirmed_at' => now()->utc(),
                    'rejected_at' => null,
                    'status_note' => null,
                ])->save();

                $eventKey = 'carrier_confirmed';
                $to = 'carrier_confirmed';
                $note = 'Carrier confirmed the booking with reference '.$record->carrier_reference.'.';
            } else {
                $note = trim($validated['note']);
                $record->forceFill([
                    'status' => 'carrier_rejected',
                    'rejected_at' => now()->utc(),
                    'status_note' => $note,
                ])->save();

                $eventKey = 'carrier_rejected';
                $to = 'carrier_rejected';
            }

            $record->events()->create([
                'actor_id' => $user->id,
                'event_key' => $eventKey,
                'from_status' => $from,
                'to_status' => $to,
                'note' => $note,
                'metadata' => ['channel' => 'carrier_portal'],
            ]);

            return $record->id;
        }, 3);

        return redirect()
            ->route('carrier.freight-bookings.show', $updated)
            ->with('carrier_status', __('carrier_freight_bookings.decision_saved'));
    }

    private function partnership(Request $request)
    {
        $user = $request->user();
        abort_unless($user && $user->hasVerifiedEmail(), 403);

        $partnership = $user->carrierPartnership()->with('carrier')->first();
        abort_unless($partnership?->carrier?->is_active, 403);

        return $partnership;
    }
}
