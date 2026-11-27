<?php

namespace App\Services\Revenue;

use App\Models\Deal;
use App\Models\DealDocument;
use App\Models\DealDocumentEvent;
use App\Models\Carrier;
use App\Models\FreightBookingOffer;
use App\Models\FreightRateBooking;
use App\Models\FreightSailing;
use App\Models\Quotation;
use App\Models\QuotationOffer;
use App\Models\Shipment;
use App\Models\ShipmentEvent;
use App\Services\Freight\SailingCatalog;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class DealExecutionHandoffService
{
    public function __construct(
        private readonly DealDocumentIntegrityVerifier $documentVerifier,
        private readonly DealIntegrityVerifier $dealVerifier,
        private readonly SailingCatalog $sailingCatalog,
    ) {
    }

    /**
     * Validate a signed Fixture Recap and return the operational source records.
     * The financial values in the signed document are never used to recalculate
     * the deal commission or any invoice amount.
     *
     * @return array<string, mixed>
     */
    public function context(DealDocument $candidate): array
    {
        $document = DealDocument::query()
            ->with(['deal.customer', 'deal.quotation.originPort', 'deal.quotation.destinationPort', 'parties'])
            ->findOrFail($candidate->getKey());

        if ($document->type !== 'fixture_recap' || $document->status !== 'signed'
            || $document->locked_at === null || $document->signed_at === null) {
            $this->reject('document_not_fully_signed');
        }

        $documentIntegrity = $this->documentVerifier->verify($document);
        if (! $documentIntegrity['valid']) {
            $this->reject('document_integrity_failed');
        }

        $deal = $document->deal;
        if (! $deal || $deal->status !== 'approved' || $deal->approved_at === null) {
            $this->reject('deal_not_approved');
        }

        $dealIntegrity = $this->dealVerifier->verify($deal);
        if (! $dealIntegrity['valid']) {
            $this->reject('deal_integrity_failed');
        }

        $quotation = $deal->quotation;
        if (! $quotation || (int) $quotation->customer_id !== (int) $deal->customer_id
            || ! $quotation->originPort || ! $quotation->destinationPort) {
            $this->reject('quotation_route_missing');
        }

        $content = (array) $document->content;
        foreach (['commodity', 'quantity', 'quantity_unit', 'loading_port', 'discharge_port', 'laycan_from', 'laycan_to'] as $field) {
            if (trim((string) ($content[$field] ?? '')) === '') {
                $this->reject('fixture_recap_incomplete');
            }
        }

        try {
            // The existing sailing workflow records schedule timestamps in UTC after
            // interpreting administrator input in Asia/Dubai. Fixture Recap
            // date-only Laycan boundaries must use the same operational zone.
            $laycanFrom = CarbonImmutable::parse((string) $content['laycan_from'], 'Asia/Dubai')->startOfDay();
            $laycanTo = CarbonImmutable::parse((string) $content['laycan_to'], 'Asia/Dubai')->endOfDay();
        } catch (\Throwable) {
            $this->reject('fixture_recap_incomplete');
        }

        if ($laycanTo->lt($laycanFrom)) {
            $this->reject('fixture_recap_incomplete');
        }

        if (! $this->fixturePortMatches((string) $content['loading_port'], $quotation->originPort)
            || ! $this->fixturePortMatches((string) $content['discharge_port'], $quotation->destinationPort)) {
            $this->reject('fixture_route_mismatch');
        }

        $legacyBooking = FreightBookingOffer::query()
            ->where('quotation_id', $quotation->getKey())
            ->whereNotNull('accepted_at')
            ->whereNotNull('confirmed_at')
            ->where('accepted_at', '<=', now())
            ->where('confirmed_at', '<=', now())
            ->first();
        $legacyCarrier = null;

        if ($legacyBooking) {
            if (trim((string) $legacyBooking->carrier_reference) === '') {
                $this->reject('booking_source_invalid');
            }

            $legacyApprovedOffer = $quotation->approved_offer_id
                ? QuotationOffer::query()
                    ->whereKey($quotation->approved_offer_id)
                    ->where('quotation_id', $quotation->getKey())
                    ->where('status', 'approved')
                    ->first()
                : null;

            if (! $legacyApprovedOffer || ! $legacyApprovedOffer->carrier_id) {
                $this->reject('booking_source_invalid');
            }

            $legacyCarrier = Carrier::query()->find($legacyApprovedOffer->carrier_id);
            if (! $legacyCarrier) {
                $this->reject('booking_source_invalid');
            }

            $conflictingRateBooking = FreightRateBooking::query()
                ->where('quotation_id', $quotation->getKey())
                ->where('status', 'carrier_confirmed')
                ->whereNotNull('confirmed_at')
                ->where('confirmed_at', '<=', now())
                ->where(function ($query) use ($legacyCarrier, $legacyBooking): void {
                    $query->whereNull('carrier_id')
                        ->orWhere('carrier_id', '!=', $legacyCarrier->getKey())
                        ->orWhereNull('carrier_reference')
                        ->orWhere('carrier_reference', '!=', $legacyBooking->carrier_reference);
                })
                ->exists();

            if ($conflictingRateBooking) {
                $this->reject('booking_sources_conflict');
            }
        }

        return [
            'document' => $document,
            'deal' => $deal,
            'quotation' => $quotation,
            'content' => $content,
            'document_integrity' => $documentIntegrity,
            'deal_integrity' => $dealIntegrity,
            'laycan_from' => $laycanFrom,
            'laycan_to' => $laycanTo,
            'legacy_booking_offer' => $legacyBooking,
            'legacy_booking_carrier' => $legacyCarrier,
        ];
    }

    /** @return \Illuminate\Support\Collection<int, FreightSailing> */
    public function availableSailings(DealDocument $document, ?array $context = null)
    {
        $context ??= $this->context($document);
        $quotation = $context['quotation'];
        $content = $context['content'];

        return $this->sailingCatalog->available()
            ->with('vessel')
            ->where('origin_port_id', $quotation->origin_port_id)
            ->where('destination_port_id', $quotation->destination_port_id)
            ->whereBetween('departure_at', [
                $context['laycan_from']->utc()->toDateTimeString(),
                $context['laycan_to']->utc()->toDateTimeString(),
            ])
            ->orderBy('departure_at')
            ->get()
            ->filter(function (FreightSailing $sailing) use ($quotation): bool {
                $containerType = trim((string) $quotation->container_type);
                $containerMatches = $containerType === ''
                    || in_array($containerType, (array) $sailing->container_types, true);

                if (! $containerMatches) {
                    return false;
                }

                if (! $quotation->freight_sailing_id) {
                    return true;
                }

                return (int) $sailing->getKey() === (int) $quotation->freight_sailing_id
                    && is_array($quotation->sailing_snapshot)
                    && $this->sailingCatalog->snapshot($sailing) == $quotation->sailing_snapshot;
            })
            ->values();
    }

    /** @return \Illuminate\Support\Collection<int, FreightRateBooking> */
    public function confirmedRateBookings(DealDocument $document, ?array $context = null)
    {
        $context ??= $this->context($document);

        return FreightRateBooking::query()
            ->with('carrier')
            ->where('quotation_id', $context['quotation']->getKey())
            ->where('status', 'carrier_confirmed')
            ->whereNotNull('confirmed_at')
            ->where('confirmed_at', '<=', now())
            ->whereNotNull('carrier_reference')
            ->where('carrier_reference', '!=', '')
            ->orderByDesc('confirmed_at')
            ->get();
    }

    public function createShipment(DealDocument $candidate, array $selection, int $actorId): Shipment
    {
        try {
            return DB::transaction(function () use ($candidate, $selection, $actorId): Shipment {
                // Lock order is document -> deal -> quotation -> optional booking/sailing.
                $document = DealDocument::query()
                    ->whereKey($candidate->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $existing = Shipment::query()
                    ->where('deal_document_id', $document->getKey())
                    ->first();
                if ($existing) {
                    return $existing;
                }

                $context = $this->context($document);
                $deal = Deal::query()->whereKey($document->deal_id)->lockForUpdate()->firstOrFail();
                $quotation = Quotation::query()->whereKey($deal->quotation_id)->lockForUpdate()->firstOrFail();
                $quotation->loadMissing(['originPort', 'destinationPort']);

                // Re-check the approved financial source and route while their rows
                // are locked. This closes the gap between the first integrity read
                // and the actual shipment insert.
                if ($deal->status !== 'approved' || $deal->approved_at === null
                    || ! $this->dealVerifier->verify($deal)['valid']) {
                    $this->reject('deal_integrity_failed');
                }
                if ((int) $quotation->customer_id !== (int) $deal->customer_id
                    || ! $quotation->originPort || ! $quotation->destinationPort) {
                    $this->reject('quotation_route_missing');
                }
                if (! $this->fixturePortMatches((string) $context['content']['loading_port'], $quotation->originPort)
                    || ! $this->fixturePortMatches((string) $context['content']['discharge_port'], $quotation->destinationPort)) {
                    $this->reject('fixture_route_mismatch');
                }

                $booking = null;
                if (! empty($selection['freight_rate_booking_id'])) {
                    $booking = FreightRateBooking::query()
                        ->whereKey($selection['freight_rate_booking_id'])
                        ->where('quotation_id', $quotation->getKey())
                        ->where('status', 'carrier_confirmed')
                        ->whereNotNull('confirmed_at')
                        ->where('confirmed_at', '<=', now())
                        ->whereNotNull('carrier_reference')
                        ->where('carrier_reference', '!=', '')
                        ->lockForUpdate()
                        ->first();

                    if (! $booking || ! $booking->carrier_id
                        || trim((string) $booking->carrier_reference) === '') {
                        $this->reject('booking_not_confirmed');
                    }
                }

                $sailingId = ! empty($selection['freight_sailing_id'])
                    ? $selection['freight_sailing_id']
                    : $quotation->freight_sailing_id;
                $sailing = null;
                if ($sailingId) {
                    $sailing = $this->sailingCatalog->available()
                        ->with('vessel')
                        ->whereKey($sailingId)
                        ->lockForUpdate()
                        ->first();

                    if (! $sailing) {
                        $this->reject('sailing_unavailable');
                    }

                    $this->assertSailingCompatible($sailing, $quotation, $context);

                    // A schedule already selected on the quotation is part of its
                    // accepted snapshot and cannot silently be replaced at handoff.
                    if ((int) $quotation->freight_sailing_id > 0) {
                        if ((int) $quotation->freight_sailing_id !== (int) $sailing->getKey()
                            || ! is_array($quotation->sailing_snapshot)
                            || $this->sailingCatalog->snapshot($sailing) != $quotation->sailing_snapshot) {
                            $this->reject('quotation_sailing_snapshot_changed');
                        }
                    }
                }

                if ($booking && $sailing && (int) $booking->carrier_id !== (int) $sailing->carrier_id) {
                    $this->reject('booking_sailing_carrier_mismatch');
                }

                $legacyOffer = FreightBookingOffer::query()
                    ->where('quotation_id', $quotation->getKey())
                    ->whereNotNull('accepted_at')
                    ->whereNotNull('confirmed_at')
                    ->where('accepted_at', '<=', now())
                    ->where('confirmed_at', '<=', now())
                    ->lockForUpdate()
                    ->first();
                $approvedOffer = $quotation->approved_offer_id
                    ? QuotationOffer::query()
                        ->whereKey($quotation->approved_offer_id)
                        ->where('quotation_id', $quotation->getKey())
                        ->where('status', 'approved')
                        ->lockForUpdate()
                        ->first()
                    : null;

                $legacyConfirmed = $legacyOffer !== null && $approvedOffer !== null;
                if ($legacyOffer && (
                    ! $approvedOffer
                    || ! $approvedOffer->carrier_id
                    || trim((string) $legacyOffer->carrier_reference) === ''
                )) {
                    // Revalidate the legacy source while its rows are locked;
                    // do not rely only on the earlier read in context().
                    $this->reject('booking_source_invalid');
                }

                $legacyCarrierId = $legacyConfirmed ? (int) $approvedOffer->carrier_id : null;
                if ($legacyConfirmed) {
                    $conflictingRateBooking = FreightRateBooking::query()
                        ->where('quotation_id', $quotation->getKey())
                        ->where('status', 'carrier_confirmed')
                        ->whereNotNull('confirmed_at')
                        ->where('confirmed_at', '<=', now())
                        ->where(function ($query) use ($legacyCarrierId, $legacyOffer): void {
                            $query->whereNull('carrier_id')
                                ->orWhere('carrier_id', '!=', $legacyCarrierId)
                                ->orWhereNull('carrier_reference')
                                ->orWhere('carrier_reference', '!=', $legacyOffer->carrier_reference);
                        })
                        ->exists();
                    if ($conflictingRateBooking) {
                        $this->reject('booking_sources_conflict');
                    }
                }
                if ($sailing && $legacyCarrierId && $legacyCarrierId !== (int) $sailing->carrier_id) {
                    $this->reject('booking_sailing_carrier_mismatch');
                }
                if ($sailing && $approvedOffer
                    && (int) $approvedOffer->carrier_id !== (int) $sailing->carrier_id) {
                    $this->reject('booking_sailing_carrier_mismatch');
                }
                if ($booking && $legacyCarrierId && $legacyCarrierId !== (int) $booking->carrier_id) {
                    $this->reject('booking_sources_conflict');
                }
                if ($booking && $approvedOffer
                    && (int) $approvedOffer->carrier_id !== (int) $booking->carrier_id) {
                    $this->reject('booking_sources_conflict');
                }

                $carrierId = $booking?->carrier_id
                    ?? $legacyCarrierId
                    ?? $approvedOffer?->carrier_id
                    ?? $sailing?->carrier_id;

                $bookingReference = $booking?->carrier_reference
                    ?? ($legacyConfirmed ? $legacyOffer->carrier_reference : null);
                $bookingConfirmed = $booking !== null || $legacyConfirmed;

                if ($bookingReference !== null && Shipment::query()
                    ->where('booking_no', $bookingReference)
                    ->exists()) {
                    $this->reject('booking_reference_already_used');
                }

                $shipmentNo = 'HAG-SHP-' . Str::upper((string) Str::ulid());
                $shipment = new Shipment;
                $shipment->forceFill([
                    'customer_id' => $deal->customer_id,
                    'quotation_id' => $quotation->getKey(),
                    'shipment_no' => $shipmentNo,
                    'booking_no' => $bookingReference,
                    'origin_port_id' => $quotation->origin_port_id,
                    'destination_port_id' => $quotation->destination_port_id,
                    'carrier_id' => $carrierId,
                    'vessel_id' => $sailing?->vessel_id,
                    // The signed Fixture Recap is the authoritative cargo description.
                    'cargo_type' => $context['content']['commodity'],
                    'container_type' => $quotation->container_type,
                    'quantity' => max(1, (int) ($quotation->quantity ?: 1)),
                    'weight' => $quotation->weight,
                    'status' => $bookingConfirmed ? 'booked' : 'pending',
                    'preferred_date' => $context['laycan_from']->toDateString(),
                    'etd' => $sailing?->departure_at,
                    'eta' => $sailing?->arrival_at,
                    'deal_id' => $deal->getKey(),
                    'deal_document_id' => $document->getKey(),
                    'freight_rate_booking_id' => $booking?->getKey(),
                    'freight_booking_offer_id' => $legacyConfirmed ? $legacyOffer->getKey() : null,
                    'freight_sailing_id' => $sailing?->getKey(),
                    'fixture_recap_sha256' => $document->document_sha256,
                    'fixture_recap_snapshot' => $this->fixtureSnapshot($document),
                    'fixture_recap_snapshot_sha256' => $this->sha256($this->fixtureSnapshot($document)),
                ])->save();

                ShipmentEvent::query()->create([
                    'shipment_id' => $shipment->getKey(),
                    'event_type' => 'deal_handoff_completed',
                    'location' => $quotation->originPort?->name,
                    'description' => 'Execution created from signed Fixture Recap ' . $document->reference
                        . ' (SHA-256 ' . $document->document_sha256 . ').',
                    'event_date' => now(),
                    'source' => 'deal_execution_handoff',
                ]);

                $this->recordDocumentEvent($document, $actorId, 'shipment.execution_created', [
                    'shipment_id' => (int) $shipment->getKey(),
                    'shipment_no' => $shipment->shipment_no,
                    'shipment_status' => $shipment->status,
                    'freight_rate_booking_id' => $booking?->getKey(),
                    'freight_booking_offer_id' => $legacyConfirmed ? $legacyOffer->getKey() : null,
                    'freight_sailing_id' => $sailing?->getKey(),
                    'carrier_id' => $carrierId,
                    'fixture_recap_sha256' => $document->document_sha256,
                ]);

                return $shipment->fresh();
            }, 3);
        } catch (QueryException $exception) {
            // The unique source-document constraint is the final concurrency guard.
            $existing = Shipment::query()
                ->where('deal_document_id', $candidate->getKey())
                ->first();
            if ($existing && $this->isUniqueConstraintViolation($exception)) {
                return $existing;
            }

            throw $exception;
        }
    }


    /**
     * Re-check the stored execution snapshot against both its hash and the
     * current, independently verified signed source document.
     *
     * @return array{valid: bool, issues: array<int, string>}
     */
    public function verifyShipmentSnapshot(Shipment $shipment, DealDocument $document): array
    {
        $issues = [];
        $document->loadMissing(['parties', 'deal']);
        $snapshot = $shipment->fixture_recap_snapshot;

        $sourceDeal = $document->deal;
        if (! $sourceDeal
            || (int) $shipment->deal_id !== (int) $document->deal_id
            || (int) $shipment->quotation_id !== (int) $sourceDeal->quotation_id
            || (int) $shipment->customer_id !== (int) $sourceDeal->customer_id
            || (int) $shipment->origin_port_id !== (int) $sourceDeal->quotation?->origin_port_id
            || (int) $shipment->destination_port_id !== (int) $sourceDeal->quotation?->destination_port_id) {
            $issues[] = 'execution_source_link_mismatch';
        }

        if ($document->type !== 'fixture_recap' || $document->status !== 'signed'
            || $document->locked_at === null || $document->signed_at === null) {
            $issues[] = 'fixture_recap_source_not_signed';
        }

        if (! is_array($snapshot) || $snapshot === []) {
            $issues[] = 'fixture_recap_snapshot_missing';
        } else {
            $actualHash = $this->sha256($snapshot);
            if (! is_string($shipment->fixture_recap_snapshot_sha256)
                || ! hash_equals((string) $shipment->fixture_recap_snapshot_sha256, $actualHash)) {
                $issues[] = 'fixture_recap_snapshot_hash_mismatch';
            }

            if (! hash_equals(
                (string) $shipment->fixture_recap_sha256,
                (string) $document->document_sha256
            )) {
                $issues[] = 'fixture_recap_source_hash_mismatch';
            }

            $documentIntegrity = $this->documentVerifier->verify($document);
            if (! ($documentIntegrity['valid'] ?? false)) {
                $issues[] = 'fixture_recap_document_integrity_failed';
            }

            $expectedSnapshot = $this->fixtureSnapshot($document);
            if (! hash_equals($this->sha256($expectedSnapshot), $actualHash)) {
                $issues[] = 'fixture_recap_snapshot_content_mismatch';
            }
        }

        $issues = array_values(array_unique($issues));

        return [
            'valid' => $issues === [],
            'issues' => $issues,
        ];
    }

    private function fixturePortMatches(string $fixtureValue, mixed $port): bool
    {
        if (! $port) {
            return false;
        }

        $normalize = static function (string $value): string {
            $value = mb_strtolower(trim($value));
            $value = preg_replace('/[^\p{L}\p{N}]+/u', '', $value) ?? '';
            // The seeded port catalog uses both "Jebel Ali Port" and "Port of Sohar".
            // Fixture Recaps often omit the generic word "Port"; normalize only
            // these conventional prefixes/suffixes, never approximate the name.
            $value = preg_replace('/^portof/u', '', $value) ?? $value;
            $value = preg_replace('/^port/u', '', $value) ?? $value;
            $value = preg_replace('/port$/u', '', $value) ?? $value;
            return $value;
        };

        // Fixture Recaps commonly qualify a port with a country after a comma
        // or in parentheses (for example, "Jebel Ali, UAE"). Compare the port
        // component, not a fuzzy substring, to avoid silently changing routes.
        $portComponent = preg_split('/[,([]/u', $fixtureValue, 2)[0] ?? $fixtureValue;
        $actual = $normalize($portComponent);
        $name = $normalize((string) $port->name);
        $unlocode = $normalize((string) ($port->unlocode ?? ''));

        return $actual !== '' && ($actual === $name || ($unlocode !== '' && $actual === $unlocode));
    }

    private function assertSailingCompatible(
        FreightSailing $sailing,
        Quotation $quotation,
        array $context
    ): void {
        if ((int) $sailing->origin_port_id !== (int) $quotation->origin_port_id
            || (int) $sailing->destination_port_id !== (int) $quotation->destination_port_id
            || $sailing->departure_at === null
            || $sailing->departure_at->lt($context['laycan_from'])
            || $sailing->departure_at->gt($context['laycan_to'])) {
            $this->reject('sailing_outside_laycan_or_route');
        }

        $containerType = trim((string) $quotation->container_type);
        if ($containerType !== '' && ! in_array($containerType, (array) $sailing->container_types, true)) {
            $this->reject('sailing_container_type_mismatch');
        }
    }

    private function fixtureSnapshot(DealDocument $document): array
    {
        return [
            'reference' => $document->reference,
            'document_sha256' => $document->document_sha256,
            'signed_at' => $document->signed_at?->toIso8601String(),
            'content' => $document->content,
            'parties' => $document->parties
                ->sortBy('signing_order')
                ->map(fn ($party): array => [
                    'role_key' => $party->role_key,
                    'company_name' => $party->company_name,
                    'contact_name' => $party->contact_name,
                    'email' => $party->email,
                    'signed_by_name' => $party->signed_by_name,
                    'signed_by_title' => $party->signed_by_title,
                    'signed_at' => $party->signed_at?->toIso8601String(),
                    'signature_evidence_sha256' => $party->signature_evidence_sha256,
                ])->values()->all(),
        ];
    }

    private function recordDocumentEvent(
        DealDocument $document,
        int $actorId,
        string $event,
        array $payload
    ): void {
        $hashPayload = [
            'deal_document_id' => (int) $document->getKey(),
            'actor_id' => $actorId,
            'event' => $event,
            'payload' => $payload,
        ];

        DealDocumentEvent::query()->create([
            'deal_document_id' => $document->getKey(),
            'actor_id' => $actorId,
            'event' => $event,
            'payload' => $payload,
            'payload_sha256' => hash('sha256', $this->serialize($hashPayload)),
        ]);
    }

    private function serialize(array $value): string
    {
        return json_encode(
            $this->canonicalize($value),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
        );
    }

    /** Sort object keys recursively while preserving list order. */
    private function canonicalize(array $value): array
    {
        if (! array_is_list($value)) {
            ksort($value, SORT_STRING);
        }

        foreach ($value as $key => $item) {
            if (is_array($item)) {
                $value[$key] = $this->canonicalize($item);
            }
        }

        return $value;
    }

    private function sha256(array $value): string
    {
        return hash('sha256', $this->serialize($value));
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        return in_array((string) $exception->getCode(), ['23000', '23505'], true)
            || in_array((int) ($exception->errorInfo[1] ?? 0), [19, 2067], true);
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages([
            'execution' => [__('deal_execution.' . $message)],
        ]);
    }
}
