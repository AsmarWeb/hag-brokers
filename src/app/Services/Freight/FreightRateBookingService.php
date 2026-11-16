<?php

namespace App\Services\Freight;

use App\Models\Customer;
use App\Models\FreightRate;
use App\Models\FreightRateBooking;
use App\Models\Quotation;
use App\Models\User;
use App\Services\FreightQuotationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class FreightRateBookingService
{
    public function __construct(private readonly FreightQuotationService $quotations)
    {
    }

    /**
     * Convert a displayed rate into a customer quotation and an immutable
     * financial snapshot. The client never supplies or controls any price.
     *
     * @return array{booking: FreightRateBooking, quotation: Quotation, created: bool}
     */
    public function select(User $actor, FreightRate $candidate, array $input): array
    {
        return DB::transaction(function () use ($actor, $candidate, $input) {
            $customer = $this->quotations->customer($actor);
            Customer::query()->whereKey($customer->id)->lockForUpdate()->firstOrFail();

            $rate = FreightRate::query()
                ->with(['source', 'carrier', 'originPort', 'destinationPort', 'sailing'])
                ->lockForUpdate()
                ->findOrFail($candidate->id);

            $quantity = (int) $input['quantity'];

            $payload = [
                'rate_id' => (int) $rate->id,
                'quantity' => $quantity,
                'preferred_date' => $input['preferred_date'] ?? null,
                'cargo_type' => trim($input['cargo_type'] ?? 'General cargo'),
            ];
            $payloadHash = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR));
            $requestKey = $input['request_key'];

            $existing = FreightRateBooking::query()
                ->where('customer_id', $customer->id)
                ->where('request_key', $requestKey)
                ->first();

            if ($existing) {
                if (! hash_equals($existing->payload_hash, $payloadHash)) {
                    throw ValidationException::withMessages([
                        'request_key' => [__('freight_selection.idempotency_conflict')],
                    ]);
                }

                return [
                    'booking' => $existing,
                    'quotation' => Quotation::query()->where('customer_id', $customer->id)->findOrFail($existing->quotation_id),
                    'created' => false,
                ];
            }

            $this->assertSelectable($rate, $quantity);

            $quotationPayload = [
                'origin_port_id' => (int) $rate->origin_port_id,
                'destination_port_id' => (int) $rate->destination_port_id,
                'cargo_type' => $payload['cargo_type'],
                'container_type' => $rate->container_type,
                'quantity' => $quantity,
                'preferred_date' => $payload['preferred_date'],
            ];

            if ($rate->freight_sailing_id) {
                $sailing = app(SailingCatalog::class)->available()->whereKey($rate->freight_sailing_id)->first();
                if (! $sailing || (int) $sailing->carrier_id !== (int) $rate->carrier_id
                    || (int) $sailing->origin_port_id !== (int) $rate->origin_port_id
                    || (int) $sailing->destination_port_id !== (int) $rate->destination_port_id
                    || ! in_array($rate->container_type, $sailing->container_types, true)) {
                    throw ValidationException::withMessages([
                        'rate' => [__('freight_selection.sailing_unavailable')],
                    ]);
                }
                $quotationPayload['freight_sailing_id'] = $sailing->id;
                $quotationPayload['sailing_revision'] = app(SailingCatalog::class)->revision($sailing);
            }

            [$quotation] = $this->quotations->submit($actor, $quotationPayload, (string) Str::uuid());

            // Keep the financial basis private and immutable even if the source rate changes later.
            $supplierSurcharges = collect($rate->surcharge_items ?? [])
                ->sum(fn (array $item) => (float) ($item['supplier_amount'] ?? $item['customer_amount'] ?? $item['amount'] ?? 0));
            $customerSurcharges = collect($rate->surcharge_items ?? [])
                ->sum(fn (array $item) => (float) ($item['customer_amount'] ?? $item['amount'] ?? 0));
            $supplierTotal = round(((float) $rate->supplier_unit_cost * $quantity) + $supplierSurcharges, 2);
            $customerTotal = round(((float) $rate->customer_unit_price * $quantity) + $customerSurcharges, 2);
            $commissionTotal = round($customerTotal - $supplierTotal, 2);

            $snapshot = [
                'rate_id' => $rate->id,
                'source' => ['code' => $rate->source->code, 'name' => $rate->source->name, 'type' => $rate->source->type],
                'carrier' => $rate->carrier ? ['id' => $rate->carrier->id, 'name' => $rate->carrier->name] : null,
                'origin' => ['id' => $rate->originPort->id, 'name' => $rate->originPort->name, 'unlocode' => $rate->originPort->unlocode],
                'destination' => ['id' => $rate->destinationPort->id, 'name' => $rate->destinationPort->name, 'unlocode' => $rate->destinationPort->unlocode],
                'container_type' => $rate->container_type,
                'quantity' => $quantity,
                'customer_unit_price' => number_format((float) $rate->customer_unit_price, 2, '.', ''),
                'customer_surcharges' => $customerSurcharges,
                'customer_total' => number_format($customerTotal, 2, '.', ''),
                'currency' => $rate->currency,
                'surcharge_items' => $rate->surcharge_items ?? [],
                'included_charges' => $rate->included_charges ?? [],
                'excluded_charges' => $rate->excluded_charges ?? [],
                'rate_type' => $rate->rate_type,
                'booking_mode' => $rate->booking_mode,
                'quoted_at' => $rate->quoted_at?->toIso8601String(),
                'expires_at' => $rate->expires_at?->toIso8601String(),
                'terms' => $rate->terms,
            ];

            $booking = FreightRateBooking::query()->create([
                'quotation_id' => $quotation->id,
                'customer_id' => $customer->id,
                'rate_id' => $rate->id,
                'source_id' => $rate->source_id,
                'carrier_id' => $rate->carrier_id,
                'request_key' => $requestKey,
                'payload_hash' => $payloadHash,
                'status' => 'request_pending',
                'currency' => $rate->currency,
                'supplier_total' => $supplierTotal,
                'customer_total' => $customerTotal,
                'commission_total' => $commissionTotal,
                'rate_snapshot' => $snapshot,
                'submitted_at' => now()->utc(),
            ]);

            $booking->events()->create([
                'actor_id' => $actor->id,
                'event_key' => 'request_submitted',
                'from_status' => null,
                'to_status' => 'request_pending',
                'note' => 'Customer submitted a freight rate selection request.',
                'metadata' => ['source' => 'customer_rate_selection'],
            ]);

            return ['booking' => $booking, 'quotation' => $quotation, 'created' => true];
        }, 3);
    }

    private function assertSelectable(FreightRate $rate, int $quantity): void
    {
        if ($rate->status !== 'published'
            || ! $rate->source?->is_active
            || ($rate->valid_from && $rate->valid_from->isFuture())
            || ($rate->expires_at && ! $rate->expires_at->isFuture())
            || $quantity < (int) $rate->min_quantity
            || ($rate->max_quantity !== null && $quantity > (int) $rate->max_quantity)
            || ! $rate->originPort?->is_active
            || ! $rate->destinationPort?->is_active) {
            throw ValidationException::withMessages([
                'rate' => [__('freight_selection.rate_unavailable')],
            ]);
        }

        if ($rate->booking_mode === 'instant_api') {
            // A rate's declared mode is not proof of a connected confirmation adapter.
            // Until an adapter is implemented and verified, selection remains a request.
        }
    }
}
