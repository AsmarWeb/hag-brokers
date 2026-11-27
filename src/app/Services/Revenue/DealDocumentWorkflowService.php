<?php

namespace App\Services\Revenue;

use App\Models\Deal;
use App\Models\DealAgreement;
use App\Models\DealCommission;
use App\Models\DealDocument;
use App\Models\DealDocumentEvent;
use App\Models\DealDocumentParty;
use App\Models\DealDocumentVersion;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

final class DealDocumentWorkflowService
{
    private const TYPES = ['ncnda', 'fixture_recap', 'commission_addendum'];

    public function createDraft(Deal $deal, array $data, int $actorId): DealDocument
    {
        return DB::transaction(function () use ($deal, $data, $actorId): DealDocument {
            $lockedDeal = Deal::query()
                ->whereKey($deal->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $type = (string) ($data['type'] ?? '');
            $this->assertType($type);

            $snapshot = $type === 'commission_addendum'
                ? $this->approvedCommissionSnapshot($lockedDeal)
                : null;

            $content = $this->normalizeContent($type, (array) ($data['content'] ?? []));
            $document = DealDocument::query()->create([
                'deal_id' => $lockedDeal->getKey(),
                'reference' => $this->newReference(),
                'type' => $type,
                'title' => trim((string) $data['title']),
                'language' => (string) $data['language'],
                'status' => 'draft',
                'content' => $content,
                'financial_snapshot' => $snapshot,
                'financial_snapshot_sha256' => $snapshot === null
                    ? null
                    : $this->sha256($snapshot),
                'template_version' => '1.0',
                'created_by' => $actorId,
                'updated_by' => $actorId,
            ]);

            $this->syncParties($document, (array) $data['parties']);
            $this->recordVersion($document, $actorId);
            $this->recordEvent($document, $actorId, 'draft.created', [
                'type' => $type,
                'reference' => $document->reference,
            ]);

            return $document->fresh(['deal', 'parties', 'versions', 'events']);
        }, 3);
    }

    public function updateDraft(DealDocument $document, array $data, int $actorId): DealDocument
    {
        return DB::transaction(function () use ($document, $data, $actorId): DealDocument {
            $locked = DealDocument::query()
                ->whereKey($document->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'draft' || $locked->locked_at !== null) {
                throw ValidationException::withMessages([
                    'document' => 'Only an unprepared draft can be edited.',
                ]);
            }

            $this->assertDocumentIntegrity($locked);

            if ((string) $data['type'] !== (string) $locked->type) {
                throw ValidationException::withMessages([
                    'type' => 'A document type cannot be changed after creation.',
                ]);
            }

            $locked->forceFill([
                'title' => trim((string) $data['title']),
                'language' => (string) $data['language'],
                'content' => $this->normalizeContent(
                    $locked->type,
                    (array) ($data['content'] ?? [])
                ),
                'updated_by' => $actorId,
            ])->save();

            $this->syncParties($locked, (array) $data['parties']);
            $this->recordVersion($locked, $actorId);
            $this->recordEvent($locked, $actorId, 'draft.updated', [
                'version' => (int) $locked->versions()->max('version'),
            ]);

            return $locked->fresh(['deal', 'parties', 'versions', 'events']);
        }, 3);
    }

    public function prepareForSignature(DealDocument $document, int $actorId): DealDocument
    {
        return DB::transaction(function () use ($document, $actorId): DealDocument {
            $locked = DealDocument::query()
                ->whereKey($document->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status !== 'draft' || $locked->locked_at !== null) {
                throw ValidationException::withMessages([
                    'document' => 'Only an unprepared draft can be prepared for signature.',
                ]);
            }

            $this->assertDocumentIntegrity($locked);

            $parties = $locked->parties()->lockForUpdate()->get();
            if ($parties->count() < 2 || $parties->contains(fn ($party) =>
                trim((string) $party->company_name) === '' ||
                trim((string) $party->contact_name) === '' ||
                trim((string) $party->email) === ''
            )) {
                throw ValidationException::withMessages([
                    'parties' => 'At least two complete signing parties are required.',
                ]);
            }

            if ($locked->type === 'commission_addendum') {
                $current = $this->approvedCommissionSnapshot($locked->deal()->firstOrFail());
                $currentHash = $this->sha256($current);
                if (! hash_equals(
                    (string) $locked->financial_snapshot_sha256,
                    $currentHash
                )) {
                    throw ValidationException::withMessages([
                        'document' => 'The approved commission snapshot no longer matches this addendum.',
                    ]);
                }
            }

            $payload = $this->documentPayload($locked->fresh(['parties']));
            $serialized = $this->serialize($payload);
            $hash = hash('sha256', $serialized);

            $locked->forceFill([
                'status' => 'ready_for_signature',
                'document_sha256' => $hash,
                'locked_at' => now(),
                'prepared_at' => now(),
                'updated_by' => $actorId,
            ])->save();

            $this->recordEvent($locked, $actorId, 'document.prepared', [
                'document_sha256' => $hash,
                'party_count' => $parties->count(),
            ]);

            return $locked->fresh(['deal', 'parties', 'versions', 'events']);
        }, 3);
    }

    public function recordSignature(
        DealDocument $document,
        DealDocumentParty $party,
        UploadedFile $evidence,
        array $data,
        int $actorId
    ): DealDocument {
        abort_unless(
            (int) $party->deal_document_id === (int) $document->getKey(),
            404
        );

        $directory = 'deal-documents/' . $document->getKey() . '/signatures';
        $path = $evidence->store($directory, 'local');
        if (! is_string($path) || $path === '') {
            throw ValidationException::withMessages([
                'evidence' => 'Unable to store the signed document evidence.',
            ]);
        }

        try {
            $absolutePath = Storage::disk('local')->path($path);
            $evidenceHash = hash_file('sha256', $absolutePath);
        } catch (Throwable) {
            Storage::disk('local')->delete($path);
            throw ValidationException::withMessages([
                'evidence' => 'Unable to verify the uploaded signed document evidence.',
            ]);
        }

        if (! is_string($evidenceHash)) {
            Storage::disk('local')->delete($path);
            throw ValidationException::withMessages([
                'evidence' => 'Unable to verify the uploaded signed document evidence.',
            ]);
        }

        try {
            return DB::transaction(function () use (
                $document,
                $party,
                $data,
                $actorId,
                $path,
                $evidenceHash
            ): DealDocument {
                $lockedDocument = DealDocument::query()
                    ->whereKey($document->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->assertDocumentIntegrity($lockedDocument);

                if (! in_array($lockedDocument->status, [
                    'ready_for_signature',
                    'partially_signed',
                ], true) || $lockedDocument->locked_at === null) {
                    throw ValidationException::withMessages([
                        'document' => 'This document is not accepting signatures.',
                    ]);
                }

                $lockedParty = DealDocumentParty::query()
                    ->whereKey($party->getKey())
                    ->where('deal_document_id', $lockedDocument->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedParty->status === 'signed') {
                    throw ValidationException::withMessages([
                        'party' => 'A signature has already been recorded for this party.',
                    ]);
                }

                $earlierPartyPending = $lockedDocument->parties()
                    ->where('signing_order', '<', $lockedParty->signing_order)
                    ->where('status', '!=', 'signed')
                    ->exists();
                if ($earlierPartyPending) {
                    throw ValidationException::withMessages([
                        'party' => 'The previous signing party must complete their signature first.',
                    ]);
                }

                $signedAt = now();
                if (! empty($data['signed_at'])) {
                    $signedAt = \Illuminate\Support\Carbon::parse($data['signed_at']);
                }
                if ($lockedDocument->prepared_at === null ||
                    $signedAt->toDateString() < $lockedDocument->prepared_at->toDateString()) {
                    throw ValidationException::withMessages([
                        'signed_at' => 'The signature date cannot precede document preparation.',
                    ]);
                }

                $lockedParty->forceFill([
                    'status' => 'signed',
                    'signed_by_name' => trim((string) $data['signed_by_name']),
                    'signed_by_title' => trim((string) $data['signed_by_title']),
                    'signed_at' => $signedAt,
                    'signature_method' => 'uploaded_signed_copy',
                    'signature_evidence_path' => $path,
                    'signature_evidence_sha256' => $evidenceHash,
                    'signature_received_by' => $actorId,
                ])->save();

                $pending = $lockedDocument->parties()
                    ->where('status', '!=', 'signed')
                    ->exists();

                $lockedDocument->forceFill([
                    'status' => $pending ? 'partially_signed' : 'signed',
                    'signed_at' => $pending ? null : $lockedDocument->parties()->max('signed_at'),
                    'updated_by' => $actorId,
                ])->save();

                $this->recordEvent($lockedDocument, $actorId, 'party.signature_received', [
                    'party_id' => $lockedParty->getKey(),
                    'role_key' => $lockedParty->role_key,
                    'signed_by_name' => $lockedParty->signed_by_name,
                    'signed_by_title' => $lockedParty->signed_by_title,
                    'signed_at' => $lockedParty->signed_at?->toIso8601String(),
                    'signature_received_by' => $lockedParty->signature_received_by,
                    'evidence_sha256' => $evidenceHash,
                    'method' => 'uploaded_signed_copy',
                ]);

                if (! $pending) {
                    $this->recordEvent($lockedDocument, $actorId, 'document.fully_signed', [
                        'document_sha256' => $lockedDocument->document_sha256,
                    ]);
                }

                return $lockedDocument->fresh(['deal', 'parties', 'versions', 'events']);
            }, 3);
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }
    }

    /**
     * Build the addendum values exclusively from the already-approved agreement
     * and immutable commission row. This method intentionally performs no fee calculation.
     */
    private function approvedCommissionSnapshot(Deal $deal): array
    {
        if ($deal->status !== 'approved' || $deal->approved_at === null) {
            throw ValidationException::withMessages([
                'type' => 'A Commission Addendum requires an approved deal.',
            ]);
        }

        $integrity = app(DealIntegrityVerifier::class)->verify($deal);
        if (! ($integrity['valid'] ?? false)) {
            throw ValidationException::withMessages([
                'type' => 'The deal financial integrity check failed. Resolve it before creating an addendum.',
            ]);
        }

        $agreements = $deal->agreements()->where('status', 'approved')->get();
        $commissions = $deal->commissions()->get();
        if ($agreements->count() !== 1 || $commissions->count() !== 1) {
            throw ValidationException::withMessages([
                'type' => 'A single approved agreement and commission are required.',
            ]);
        }

        /** @var DealAgreement $agreement */
        $agreement = $agreements->first();
        /** @var DealCommission $commission */
        $commission = $commissions->first();

        if ((int) $commission->deal_agreement_id !== (int) $agreement->getKey()) {
            throw ValidationException::withMessages([
                'type' => 'The commission does not match the approved agreement.',
            ]);
        }

        return [
            'deal_reference' => $deal->reference,
            'agreement' => [
                'id' => $agreement->getKey(),
                'version' => (int) $agreement->version,
                'fee_kind' => $agreement->fee_kind,
                'commission_bps' => $agreement->commission_bps,
                'fixed_fee' => $agreement->fixed_fee,
                'terms' => $agreement->terms,
                'content_sha256' => $agreement->content_sha256,
                'approved_at' => $agreement->approved_at?->toIso8601String(),
            ],
            'commission' => [
                'id' => $commission->getKey(),
                'basis_amount' => $commission->basis_amount,
                'rate_bps' => $commission->rate_bps,
                'amount' => $commission->amount,
                'currency' => $commission->currency,
                'status' => $commission->status,
            ],
            'source' => 'approved_deal_agreement_and_immutable_commission',
        ];
    }

    private function syncParties(DealDocument $document, array $parties): void
    {
        $existing = $document->parties()->get()->keyBy('role_key');
        foreach (array_values($parties) as $index => $partyData) {
            $role = (string) ($partyData['role_key'] ?? ($index === 0 ? 'party_a' : 'party_b'));
            $attributes = [
                'role_key' => $role,
                'company_name' => trim((string) $partyData['company_name']),
                'contact_name' => trim((string) $partyData['contact_name']),
                'email' => strtolower(trim((string) $partyData['email'])),
                'signing_order' => $index + 1,
            ];

            if ($existing->has($role)) {
                $existing->get($role)->fill($attributes)->save();
            } else {
                $document->parties()->create($attributes + ['status' => 'pending']);
            }
        }
    }

    private function assertDocumentIntegrity(DealDocument $document): void
    {
        $integrity = app(DealDocumentIntegrityVerifier::class)->verify($document);
        if (! ($integrity['valid'] ?? false)) {
            throw ValidationException::withMessages([
                'document' => 'The document integrity check failed. Review the integrity panel before continuing.',
            ]);
        }
    }

    private function normalizeContent(string $type, array $content): array
    {
        if ($type === 'ncnda') {
            return [
                'protected_contacts' => trim((string) ($content['protected_contacts'] ?? '')),
                'term_months' => (int) ($content['term_months'] ?? 0),
                'territory' => trim((string) ($content['territory'] ?? '')),
                'effective_date' => $content['effective_date'] ?? null,
                'legal_reviewed_by' => trim((string) ($content['legal_reviewed_by'] ?? '')),
                'legal_reviewed_at' => $content['legal_reviewed_at'] ?? null,
                'agreement_text' => trim((string) ($content['agreement_text'] ?? '')),
            ];
        }

        if ($type === 'fixture_recap') {
            return [
                'commodity' => trim((string) ($content['commodity'] ?? '')),
                'quantity' => (string) ($content['quantity'] ?? ''),
                'quantity_unit' => trim((string) ($content['quantity_unit'] ?? '')),
                'loading_port' => trim((string) ($content['loading_port'] ?? '')),
                'discharge_port' => trim((string) ($content['discharge_port'] ?? '')),
                'laycan_from' => $content['laycan_from'] ?? null,
                'laycan_to' => $content['laycan_to'] ?? null,
                'vessel_name' => trim((string) ($content['vessel_name'] ?? '')),
                'freight_rate' => $content['freight_rate'] ?? null,
                'freight_currency' => strtoupper(trim((string) ($content['freight_currency'] ?? ''))),
                'freight_rate_basis' => trim((string) ($content['freight_rate_basis'] ?? '')),
                'payment_terms' => trim((string) ($content['payment_terms'] ?? '')),
                'demurrage_rate' => $content['demurrage_rate'] ?? null,
                'despatch_rate' => $content['despatch_rate'] ?? null,
                'additional_terms' => trim((string) ($content['additional_terms'] ?? '')),
            ];
        }

        if ($type === 'commission_addendum') {
            // Never accept user-supplied monetary data for this document type.
            return ['description' => 'Snapshot of the approved deal agreement and immutable commission record.'];
        }

        $this->assertType($type);
        return [];
    }

    private function documentPayload(DealDocument $document): array
    {
        $parties = $document->parties
            ->sortBy('signing_order')
            ->map(fn (DealDocumentParty $party): array => [
                'party_id' => (int) $party->getKey(),
                'role_key' => $party->role_key,
                'company_name' => $party->company_name,
                'contact_name' => $party->contact_name,
                'email' => $party->email,
                'signing_order' => (int) $party->signing_order,
            ])->values()->all();

        return [
            'deal_id' => (int) $document->deal_id,
            'reference' => $document->reference,
            'type' => $document->type,
            'title' => $document->title,
            'language' => $document->language,
            'template_version' => $document->template_version,
            'content' => $document->content,
            'financial_snapshot' => $document->financial_snapshot,
            'parties' => $parties,
        ];
    }

    private function recordVersion(DealDocument $document, int $actorId): void
    {
        $snapshot = $this->documentPayload($document->fresh(['parties']));
        $version = (int) $document->versions()->max('version') + 1;
        $hashPayload = [
            'deal_document_id' => (int) $document->getKey(),
            'version' => $version,
            'actor_id' => $actorId,
            'snapshot' => $snapshot,
        ];

        $document->versions()->create([
            'version' => $version,
            'snapshot' => $snapshot,
            'snapshot_sha256' => hash('sha256', $this->serialize($hashPayload)),
            'actor_id' => $actorId,
        ]);
    }

    private function recordEvent(
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

    /**
     * Sort object keys recursively while preserving list order. This keeps
     * SHA-256 snapshots stable across SQLite and MySQL JSON serialization.
     */
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

    private function newReference(): string
    {
        return 'HAG-DOC-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8));
    }

    private function assertType(string $type): void
    {
        if (! in_array($type, self::TYPES, true)) {
            throw ValidationException::withMessages([
                'type' => 'Unsupported deal document type.',
            ]);
        }
    }
}
