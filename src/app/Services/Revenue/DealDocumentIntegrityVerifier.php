<?php

namespace App\Services\Revenue;

use App\Models\Deal;
use App\Models\DealDocument;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class DealDocumentIntegrityVerifier
{
    /**
     * Read-only verification of document snapshots, workflow events,
     * party-signature state and uploaded evidence.
     *
     * @return array{valid: bool, issues: array<int, string>}
     */
    public function verify(DealDocument $document): array
    {
        $issues = [];
        $document->loadMissing(['deal', 'parties', 'versions', 'events']);

        foreach ($document->versions as $version) {
            $versionHashPayload = [
                'deal_document_id' => (int) $version->deal_document_id,
                'version' => (int) $version->version,
                'actor_id' => $version->actor_id === null ? null : (int) $version->actor_id,
                'snapshot' => $version->snapshot,
            ];
            if (! $this->matchesHash($versionHashPayload, $version->snapshot_sha256)) {
                $issues[] = 'version_hash_mismatch';
            }
        }
        $versionNumbers = $document->versions->pluck('version')->map(fn ($value) => (int) $value)->sort()->values()->all();
        if ($versionNumbers !== ($document->versions->isEmpty() ? [] : range(1, $document->versions->count()))) {
            $issues[] = 'version_sequence_invalid';
        }

        foreach ($document->events as $event) {
            $eventHashPayload = [
                'deal_document_id' => (int) $event->deal_document_id,
                'actor_id' => $event->actor_id === null ? null : (int) $event->actor_id,
                'event' => $event->event,
                'payload' => $event->payload,
            ];
            if (! $this->matchesHash($eventHashPayload, $event->payload_sha256)) {
                $issues[] = 'audit_hash_mismatch';
            }
        }

        $currentPayload = $this->documentPayload($document);
        $latestVersion = $document->versions->sortByDesc('version')->first();
        if (! $latestVersion ||
            ! $this->samePayload($latestVersion->snapshot, $currentPayload)) {
            $issues[] = 'latest_version_snapshot_mismatch';
        }

        if ($document->locked_at !== null) {
            if (! $this->matchesHash($currentPayload, $document->document_sha256)) {
                $issues[] = 'document_hash_mismatch';
            }
        } elseif ($document->document_sha256 !== null || $document->status !== 'draft') {
            $issues[] = 'document_lock_metadata_mismatch';
        }

        if (! in_array($document->type, ['ncnda', 'fixture_recap', 'commission_addendum'], true)) {
            $issues[] = 'document_type_invalid';
        }

        $parties = $document->parties;
        $partyRoles = $parties->pluck('role_key')->sort()->values()->all();
        if ($partyRoles !== ['party_a', 'party_b'] ||
            $parties->pluck('signing_order')->map(fn ($value) => (int) $value)->sort()->values()->all() !== [1, 2]) {
            $issues[] = 'party_identity_roles_invalid';
        }
        $partyCount = $parties->count();
        $signedParties = $parties->where('status', 'signed');
        $signedCount = $signedParties->count();

        if ($partyCount !== 2) {
            $issues[] = 'party_count_invalid';
        }

        foreach ($parties as $party) {
            if ($party->status !== 'signed') {
                if ($party->signature_evidence_path !== null ||
                    $party->signature_evidence_sha256 !== null ||
                    $party->signed_by_name !== null ||
                    $party->signed_by_title !== null ||
                    $party->signed_at !== null ||
                    $party->signature_method !== null ||
                    $party->signature_received_by !== null) {
                    $issues[] = 'pending_party_signature_metadata_invalid';
                }
                continue;
            }

            if (
                trim((string) $party->signed_by_name) === '' ||
                trim((string) $party->signed_by_title) === '' ||
                $party->signed_at === null ||
                $party->signature_method !== 'uploaded_signed_copy' ||
                $party->signature_received_by === null
            ) {
                $issues[] = 'signed_party_metadata_invalid';
            }

            $signatureEvents = $document->events->filter(
                fn ($event): bool => $event->event === 'party.signature_received'
                    && (string) ($event->payload['party_id'] ?? '') === (string) $party->getKey()
            );
            if ($signatureEvents->count() !== 1) {
                $issues[] = 'signature_audit_party_mismatch';
            } else {
                $signatureEvent = $signatureEvents->first();
                $signaturePayload = $signatureEvent->payload;
                $eventSignedAt = $signaturePayload['signed_at'] ?? null;
                $partySignedAt = $party->signed_at?->toIso8601String();
                if (
                    (string) ($signaturePayload['role_key'] ?? '') !== (string) $party->role_key ||
                    (string) ($signaturePayload['signed_by_name'] ?? '') !== (string) $party->signed_by_name ||
                    (string) ($signaturePayload['signed_by_title'] ?? '') !== (string) $party->signed_by_title ||
                    (string) $eventSignedAt !== (string) $partySignedAt ||
                    (string) ($signaturePayload['signature_received_by'] ?? '') !== (string) $party->signature_received_by ||
                    (string) ($signaturePayload['evidence_sha256'] ?? '') !== (string) $party->signature_evidence_sha256 ||
                    (string) ($signaturePayload['method'] ?? '') !== (string) $party->signature_method ||
                    (string) $signatureEvent->actor_id !== (string) $party->signature_received_by
                ) {
                    $issues[] = 'signature_audit_party_mismatch';
                }
            }

            $path = (string) $party->signature_evidence_path;
            if ($path === '' || ! preg_match(
                '~\Adeal-documents/' . preg_quote((string) $document->getKey(), '~') . '/signatures/[A-Za-z0-9._-]+\.(pdf|jpg|jpeg|png)\z~iD',
                $path
            )) {
                $issues[] = 'signature_evidence_path_invalid';
                continue;
            }

            $disk = Storage::disk('local');
            if (! $disk->exists($path)) {
                $issues[] = 'signature_evidence_missing';
                continue;
            }

            try {
                $actualHash = hash_file('sha256', $disk->path($path));
                if (! is_string($actualHash) ||
                    ! preg_match('/\A[a-f0-9]{64}\z/', (string) $party->signature_evidence_sha256) ||
                    ! hash_equals((string) $party->signature_evidence_sha256, $actualHash)) {
                    $issues[] = 'signature_evidence_hash_mismatch';
                }
            } catch (Throwable) {
                $issues[] = 'signature_evidence_unreadable';
            }
        }

        $eventNames = $document->events->pluck('event');
        if ($eventNames->countBy()->get('draft.created', 0) !== 1) {
            $issues[] = 'draft_creation_audit_invalid';
        }
        if ($eventNames->countBy()->get('draft.updated', 0) !== max(0, $document->versions->count() - 1)) {
            $issues[] = 'draft_version_audit_count_mismatch';
        }

        $preparedEventCount = $eventNames->countBy()->get('document.prepared', 0);
        if (($document->locked_at !== null && $preparedEventCount !== 1) ||
            ($document->locked_at === null && $preparedEventCount !== 0)) {
            $issues[] = 'document_preparation_audit_mismatch';
        }

        if ($eventNames->countBy()->get('party.signature_received', 0) !== $signedCount) {
            $issues[] = 'signature_audit_count_mismatch';
        }
        $fullySignedEventCount = $eventNames->countBy()->get('document.fully_signed', 0);
        if (($document->status === 'signed' && $fullySignedEventCount !== 1) ||
            ($document->status !== 'signed' && $fullySignedEventCount !== 0)) {
            $issues[] = 'fully_signed_audit_mismatch';
        }

        $statusValid = match ($document->status) {
            'draft' => $document->locked_at === null && $document->prepared_at === null && $signedCount === 0 && $document->signed_at === null,
            'ready_for_signature' => $document->locked_at !== null && $document->prepared_at !== null && $signedCount === 0 && $document->signed_at === null,
            'partially_signed' => $document->locked_at !== null && $document->prepared_at !== null && $signedCount > 0 && $signedCount < $partyCount && $document->signed_at === null,
            'signed' => $document->locked_at !== null && $document->prepared_at !== null && $partyCount > 0 && $signedCount === $partyCount && $document->signed_at !== null,
            default => false,
        };
        if (! $statusValid) {
            $issues[] = 'document_signature_state_mismatch';
        }
        if ($document->status === 'signed' && $document->signed_at !== null) {
            $latestPartySignature = $signedParties->max('signed_at');
            if ($latestPartySignature === null ||
                $document->signed_at->toDateString() !== \Illuminate\Support\Carbon::parse($latestPartySignature)->toDateString()) {
                $issues[] = 'document_signed_date_mismatch';
            }
        }

        if ($document->type === 'commission_addendum') {
            if (! $document->financial_snapshot || ! $this->matchesHash(
                $document->financial_snapshot,
                $document->financial_snapshot_sha256
            )) {
                $issues[] = 'commission_snapshot_hash_mismatch';
            }

            if ($document->deal) {
                $financialIntegrity = app(DealIntegrityVerifier::class)->verify($document->deal);
                if (! ($financialIntegrity['valid'] ?? false)) {
                    $issues[] = 'source_deal_financial_integrity_failed';
                }

                $currentSnapshot = $this->currentCommissionSnapshot($document->deal);
                if ($currentSnapshot === null ||
                    ! $this->samePayload($document->financial_snapshot ?? [], $currentSnapshot)) {
                    $issues[] = 'commission_source_snapshot_mismatch';
                }
            } else {
                $issues[] = 'commission_source_deal_missing';
            }
        } elseif ($document->financial_snapshot !== null || $document->financial_snapshot_sha256 !== null) {
            $issues[] = 'unexpected_financial_snapshot';
        }

        $issues = array_values(array_unique($issues));

        return [
            'valid' => count($issues) === 0,
            'issues' => $issues,
        ];
    }

    private function currentCommissionSnapshot(Deal $deal): ?array
    {
        if ($deal->status !== 'approved' || $deal->approved_at === null) {
            return null;
        }

        $agreements = $deal->agreements()->where('status', 'approved')->get();
        $commissions = $deal->commissions()->get();
        if ($agreements->count() !== 1 || $commissions->count() !== 1) {
            return null;
        }

        $agreement = $agreements->first();
        $commission = $commissions->first();
        if ((int) $commission->deal_agreement_id !== (int) $agreement->getKey()) {
            return null;
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

    private function documentPayload(DealDocument $document): array
    {
        $parties = $document->parties
            ->sortBy('signing_order')
            ->map(fn ($party): array => [
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

    private function samePayload(mixed $left, array $right): bool
    {
        if (! is_array($left)) {
            return false;
        }

        try {
            return $this->serialize($left) === $this->serialize($right);
        } catch (Throwable) {
            return false;
        }
    }

    private function matchesHash(mixed $payload, ?string $expectedHash): bool
    {
        if (! is_array($payload) ||
            ! is_string($expectedHash) ||
            ! preg_match('/\A[a-f0-9]{64}\z/', $expectedHash)) {
            return false;
        }

        try {
            return hash_equals($expectedHash, hash('sha256', $this->serialize($payload)));
        } catch (Throwable) {
            return false;
        }
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
}
