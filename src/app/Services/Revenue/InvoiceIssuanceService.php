<?php

namespace App\Services\Revenue;

use App\Models\CompanySetting;
use App\Models\Deal;
use App\Models\DealAgreement;
use App\Models\DealCommission;
use App\Models\InvoiceDraft;
use App\Models\IssuedInvoice;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class InvoiceIssuanceService
{
    public function issuer(): array
    {
        $company = CompanySetting::orderBy('id')->first();
        return [
            'name' => trim((string) $company?->company_name),
            'name_ar' => trim((string) $company?->company_name_ar),
            'address' => trim((string) $company?->address),
            'city' => trim((string) $company?->city),
            'country' => trim((string) $company?->country),
            'trn' => trim((string) $company?->tax_number),
            'email' => (string) $company?->email, 'phone' => (string) $company?->phone,
        ];
    }

    public function issuerReady(array $issuer): bool
    {
        return Validator::make($issuer, [
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:2000'],
            'country' => ['required', 'string', 'max:255'],
            'trn' => ['required', 'regex:/^\d{15}$/D'],
        ])->passes();
    }

    public function preview(InvoiceDraft $draft, array $data, int $actorId): array
    {
        if ($draft->issuedInvoice()->exists()) $this->reject('already_issued');
        $this->verifySource($draft);
        $issuer = $this->issuer();
        if (!$this->issuerReady($issuer)) $this->reject('issuer_missing');
        $context = $this->context($draft, $issuer, $data);
        $token = Crypt::encryptString(json_encode([
            'draft_id' => (string) $draft->id, 'actor_id' => (string) $actorId,
            'draft_hash' => $draft->snapshot_sha256, 'context' => $context,
            'expires_at' => now()->addMinutes(15)->timestamp,
        ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        return ['context' => $context, 'token' => $token];
    }

    public function sourceReady(InvoiceDraft $draft): bool
    {
        if ($draft->status !== 'draft' || !(new InvoiceDraftService())->hasValidSnapshot($draft)) return false;
        $deal = Deal::find($draft->deal_id);
        $commission = DealCommission::find($draft->deal_commission_id);
        $agreement = DealAgreement::find($draft->deal_agreement_id);
        return $deal !== null && $commission !== null && $agreement !== null
            && (string) $commission->deal_id === (string) $deal->id
            && (string) $agreement->deal_id === (string) $deal->id
            && (string) $commission->deal_agreement_id === (string) $agreement->id
            && (string) $draft->customer_id === (string) $deal->customer_id
            && (string) $draft->amount === (string) $commission->amount
            && $draft->currency === $commission->currency
            && hash_equals((string) $draft->source_agreement_sha256, (string) $agreement->content_sha256)
            && (new DealIntegrityVerifier())->verify($deal)['valid'];
    }

    public function issue(InvoiceDraft $draft, string $token, int $actorId): IssuedInvoice
    {
        try {
            $payload = json_decode(Crypt::decryptString($token), true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException $exception) {
            $this->reject('invalid_review');
        }
        if (!is_array($payload) || (string) ($payload['draft_id'] ?? '') !== (string) $draft->id
            || (string) ($payload['actor_id'] ?? '') !== (string) $actorId) $this->reject('invalid_review');
        try {
            return DB::transaction(function () use ($draft, $payload, $actorId) {
                $deal = Deal::whereKey($draft->deal_id)->lockForUpdate()->firstOrFail();
                DealAgreement::whereKey($draft->deal_agreement_id)->lockForUpdate()->firstOrFail();
                DealCommission::whereKey($draft->deal_commission_id)->lockForUpdate()->firstOrFail();
                $locked = InvoiceDraft::whereKey($draft->id)->lockForUpdate()->firstOrFail();
                $existing = IssuedInvoice::where('invoice_draft_id', $locked->id)->first();
                if ($existing) {
                    if (!$this->hasValidSnapshot($existing)) $this->reject('snapshot_invalid');
                    return $existing;
                }
                if ((int) ($payload['expires_at'] ?? 0) < now()->timestamp) $this->reject('invalid_review');
                $this->verifySource($locked);
                CompanySetting::orderBy('id')->lockForUpdate()->first();
                $issuer = $this->issuer();
                if (!$this->issuerReady($issuer)) $this->reject('issuer_missing');
                $review = $payload['context'] ?? [];
                if (!is_array($review) || !isset($review['input'])) $this->reject('invalid_review');
                $fresh = $this->context($locked, $issuer, $review['input']);
                if (!hash_equals((string) ($payload['draft_hash'] ?? ''), (string) $locked->snapshot_sha256)
                    || !hash_equals($this->hash($review), $this->hash($fresh))) $this->reject('review_changed');
                $issuedAt = now('UTC');
                if ($fresh['issue_date'] !== $issuedAt->copy()->setTimezone('Asia/Dubai')->toDateString()) $this->reject('review_changed');
                $year = (int) $issuedAt->copy()->setTimezone('Asia/Dubai')->format('Y');
                DB::table('invoice_sequences')->insertOrIgnore(['year' => $year, 'last_number' => 0]);
                $sequence = DB::table('invoice_sequences')->where('year', $year)->lockForUpdate()->first();
                $number = (int) $sequence->last_number + 1;
                DB::table('invoice_sequences')->where('year', $year)->update(['last_number' => $number]);
                $reference = 'HAG-INV-'.$year.'-'.str_pad((string) $number, 6, '0', STR_PAD_LEFT);
                $snapshot = $fresh + [
                    'reference' => $reference, 'invoice_draft_id' => (string) $locked->id,
                    'sequence_year' => (string) $year, 'sequence_number' => (string) $number,
                    'issued_at' => $issuedAt->toIso8601String(),
                    'source_draft_hash' => $locked->snapshot_sha256,
                    'draft_reference' => $locked->reference,
                    'customer_name' => data_get($locked->snapshot, 'customer.company_name'),
                    'deal_reference' => data_get($locked->snapshot, 'deal_reference'),
                ];
                return IssuedInvoice::create([
                    'invoice_draft_id' => $locked->id, 'reference' => $reference,
                    'sequence_year' => $year, 'sequence_number' => $number,
                    'currency' => $locked->currency,
                    ...collect($fresh['amounts'])->except('tax_rate_bps')->all(),
                    'supply_date' => $fresh['input']['supply_date'],
                    'issued_at' => $issuedAt, 'issued_by' => $actorId,
                    'snapshot' => $snapshot, 'snapshot_sha256' => $this->hash($snapshot),
                ]);
            }, 3);
        } catch (QueryException $exception) {
            $unique = in_array((string) $exception->getCode(), ['23000', '23505'], true)
                || in_array((int) ($exception->errorInfo[1] ?? 0), [19, 2067], true);
            if ($unique) {
                $existing = IssuedInvoice::where('invoice_draft_id', $draft->id)->first();
                if ($existing && $this->hasValidSnapshot($existing)) return $existing;
            }
            throw $exception;
        }
    }

    public function hasValidSnapshot(IssuedInvoice $invoice): bool
    {
        try {
            $snapshot = $invoice->snapshot;
            if (!is_array($snapshot) || !hash_equals((string) $invoice->snapshot_sha256, $this->hash($snapshot))) return false;
            if ((string) ($snapshot['reference'] ?? '') !== $invoice->reference
                || (string) ($snapshot['invoice_draft_id'] ?? '') !== (string) $invoice->invoice_draft_id
                || (string) ($snapshot['sequence_year'] ?? '') !== (string) $invoice->sequence_year
                || (string) ($snapshot['sequence_number'] ?? '') !== (string) $invoice->sequence_number
                || ($snapshot['currency'] ?? '') !== $invoice->currency) return false;
            foreach (['subtotal', 'tax_amount', 'total', 'subtotal_aed', 'tax_aed', 'total_aed', 'exchange_rate'] as $field) {
                if ((string) data_get($snapshot, 'amounts.'.$field) !== (string) $invoice->getAttribute($field)) return false;
            }
            return ($snapshot['input']['supply_date'] ?? '') === $invoice->supply_date->format('Y-m-d')
                && ($snapshot['issued_at'] ?? '') === \Carbon\CarbonImmutable::parse($invoice->getRawOriginal('issued_at'), 'UTC')->toIso8601String();
        } catch (\Throwable $exception) {
            return false;
        }
    }

    private function context(InvoiceDraft $draft, array $issuer, array $data): array
    {
        // Apply the same validation for preview and issue; never trust hidden monetary inputs.
        $today = now('Asia/Dubai')->toDateString();
        $rules = [
            'supply_date' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.$today],
            'due_date' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.$today],
            'tax_treatment' => ['required', 'string', 'in:standard,zero'],
            'amount_basis' => ['required', 'string', 'in:exclusive,inclusive'],
            'tax_reason' => ['nullable', 'string', 'max:1000', 'required_if:tax_treatment,zero'],
            'recipient_address' => ['required', 'string', 'max:2000'],
            'recipient_registered' => ['required', 'string', 'in:yes,no'],
            'recipient_trn' => ['nullable', 'string', 'regex:/^\d{15}$/D', 'required_if:recipient_registered,yes', 'prohibited_if:recipient_registered,no'],
            'description_ar' => ['required', 'string', 'max:1000'],
            'description_en' => ['required', 'string', 'max:1000'],
            'public_notes' => ['nullable', 'string', 'max:2000'],
        ];
        if ($draft->currency !== 'AED') {
            $rules += [
                'exchange_rate' => ['required', 'string', 'regex:/^(?:0|[1-9]\d{0,5})(?:\.\d{1,8})?$/D', 'gt:0'],
                'exchange_rate_date' => ['required', 'date_format:Y-m-d', 'same:supply_date'],
                'exchange_rate_reference' => ['required', 'string', 'max:500'],
            ];
        }
        $input = Validator::make($data, $rules)->validate();
        // Store only a fixed set of fields to avoid retaining injected financial or internal values.
        $input = array_replace(array_fill_keys(array_keys($rules), null), $input);
        $rate = $draft->currency === 'AED' ? '1.00000000' : (string) $input['exchange_rate'];
        try {
            $amounts = (new InvoiceAmounts())->calculate((string) $draft->amount, $input['amount_basis'], $input['tax_treatment'], $rate);
        } catch (\InvalidArgumentException|\Brick\Math\Exception\MathException $exception) {
            $this->reject('invalid_amounts');
        }
        return [
            'version' => 1, 'document_type' => 'tax_invoice',
            'issue_date' => $today, 'timezone' => 'Asia/Dubai',
            'issuer' => $issuer, 'input' => $input, 'currency' => $draft->currency,
            'commission_amount' => (string) $draft->amount, 'quantity' => 1,
            'discount' => '0.00', 'amounts' => $amounts,
        ];
    }

    private function verifySource(InvoiceDraft $draft): void
    {
        if (!$this->sourceReady($draft)) $this->reject('source_invalid');
    }

    private function hash(array $value): string
    {
        $sort = function (array $data) use (&$sort): array {
            foreach ($data as $key => $item) if (is_array($item)) $data[$key] = $sort($item);
            ksort($data); return $data;
        };
        return hash('sha256', json_encode($sort($value), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
    }

    private function reject(string $key): never
    {
        throw ValidationException::withMessages(['invoice' => __('invoices.'.$key)]);
    }
}
