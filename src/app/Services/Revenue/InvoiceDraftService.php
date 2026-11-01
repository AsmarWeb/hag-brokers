<?php

namespace App\Services\Revenue;

use App\Models\Deal;
use App\Models\DealAgreement;
use App\Models\DealCommission;
use App\Models\InvoiceDraft;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class InvoiceDraftService
{
    public function create(DealCommission $commission, int $actorId): InvoiceDraft
    {
        try {
            return DB::transaction(function () use ($commission, $actorId) {
                // Match approval lock order: deal, agreement, commission.
                $deal = Deal::whereKey($commission->deal_id)->lockForUpdate()->firstOrFail();
                $agreement = DealAgreement::whereKey($commission->deal_agreement_id)
                    ->where('deal_id', $deal->id)->lockForUpdate()->firstOrFail();
                $locked = DealCommission::whereKey($commission->id)->where('deal_id', $deal->id)
                    ->where('deal_agreement_id', $agreement->id)->lockForUpdate()->firstOrFail();
                $verification = (new DealIntegrityVerifier())->verify($deal);
                if (!$verification['valid']) $this->reject('source_invalid');
                // Current financial integrity version accepts pending commissions only.
                if ($locked->status !== 'pending' || $agreement->status !== 'approved' || $deal->status !== 'approved') {
                    $this->reject('source_invalid');
                }
                $existing = InvoiceDraft::where('deal_commission_id', $locked->id)->first();
                if ($existing) {
                    if (!$this->hasValidSnapshot($existing)) $this->reject('snapshot_invalid');
                    return $existing;
                }
                $deal->load('customer');
                if (!$deal->customer) $this->reject('source_invalid');
                $reference = 'HAG-DRAFT-'.Str::upper((string) Str::ulid());
                $amount = ReportMoney::normalize((string) $locked->amount);
                $snapshot = [
                    'version' => 1, 'reference' => $reference, 'status' => 'draft',
                    'deal_commission_id' => (string) $locked->id,
                    'deal_id' => (string) $deal->id,
                    'deal_agreement_id' => (string) $agreement->id,
                    'customer_id' => (string) $deal->customer_id,
                    'deal_reference' => $deal->reference,
                    'agreement_version' => (int) $agreement->version,
                    'agreement_hash' => (string) $agreement->content_sha256,
                    'currency' => (string) $locked->currency,
                    'amount' => $amount,
                    'fee_kind' => $agreement->fee_kind,
                    'customer' => $deal->customer->only(['company_name', 'contact_person', 'email', 'country_code', 'city']),
                ];
                return InvoiceDraft::create([
                    'reference' => $reference, 'deal_commission_id' => $locked->id,
                    'deal_id' => $deal->id, 'deal_agreement_id' => $agreement->id,
                    'customer_id' => $deal->customer_id, 'status' => 'draft',
                    'currency' => $locked->currency, 'amount' => $amount,
                    'source_agreement_sha256' => $agreement->content_sha256,
                    'snapshot' => $snapshot, 'snapshot_sha256' => $this->hash($snapshot),
                    'created_by' => $actorId,
                ]);
            }, 3);
        } catch (QueryException $exception) {
            // A database unique constraint is the final guard against concurrent duplicates.
            $uniqueConflict = in_array((string) $exception->getCode(), ['23000', '23505'], true)
                || in_array((int) ($exception->errorInfo[1] ?? 0), [19, 2067], true);
            if ($uniqueConflict) {
                $existing = InvoiceDraft::where('deal_commission_id', $commission->id)->first();
                if ($existing && $this->hasValidSnapshot($existing)) return $existing;
            }
            throw $exception;
        }
    }

    public function updateMetadata(InvoiceDraft $draft, array $data): InvoiceDraft
    {
        return DB::transaction(function () use ($draft, $data) {
            $locked = InvoiceDraft::whereKey($draft->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'draft' || !$this->hasValidSnapshot($locked)) $this->reject('snapshot_invalid');
            $locked->update([
                'notes' => $data['notes'] ?? null,
                'due_date' => $data['due_date'] ?? null,
            ]);
            return $locked;
        }, 3);
    }

    public function hasValidSnapshot(InvoiceDraft $draft): bool
    {
        $snapshot = $draft->snapshot;
        if (!is_array($snapshot) || !hash_equals((string) $draft->snapshot_sha256, $this->hash($snapshot))) return false;
        foreach (['reference', 'status', 'deal_commission_id', 'deal_id', 'deal_agreement_id', 'customer_id', 'currency', 'amount'] as $field) {
            if ((string) ($snapshot[$field] ?? '') !== (string) $draft->getAttribute($field)) return false;
        }
        return (string) ($snapshot['agreement_hash'] ?? '') === (string) $draft->source_agreement_sha256;
    }

    private function hash(array $snapshot): string
    {
        // Canonical key order also works when a database reorders native JSON object keys.
        $sort = function (array $value) use (&$sort): array {
            foreach ($value as $key => $item) if (is_array($item)) $value[$key] = $sort($item);
            ksort($value);
            return $value;
        };
        return hash('sha256', json_encode($sort($snapshot), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    private function reject(string $message): never
    {
        throw ValidationException::withMessages(['invoice' => __('invoice_drafts.'.$message)]);
    }
}
