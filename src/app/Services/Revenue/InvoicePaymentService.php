<?php

namespace App\Services\Revenue;

use App\Models\InvoicePayment;
use App\Models\IssuedInvoice;
use App\Models\User;
use Brick\Math\BigDecimal;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class InvoicePaymentService
{
    private function fail(string $key): never
    {
        throw ValidationException::withMessages(['payment' => __('payments.'.$key)]);
    }

    private function digest(array $data): string
    {
        ksort($data);
        return hash('sha256', json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private function validEvent(InvoicePayment $event, IssuedInvoice $invoice): bool
    {
        try {
            $snapshot = $event->snapshot;
            if (!is_array($snapshot) || !hash_equals($event->snapshot_sha256, $this->digest($snapshot))) return false;
            foreach (['event_uuid', 'issued_invoice_id', 'receipt_reference_hash', 'kind', 'reverses_id', 'request_key', 'request_hash', 'amount', 'currency', 'method', 'reference', 'notes', 'reason', 'recorded_by', 'recorded_by_name'] as $key) {
                if ((string) $event->getRawOriginal($key) !== (string) ($snapshot[$key] ?? '')) return false;
            }
            if ($event->paid_on->format('Y-m-d') !== ($snapshot['paid_on'] ?? null)) return false;
            if ($event->recorded_at->format('Y-m-d H:i:s') !== ($snapshot['recorded_at'] ?? null)) return false;
            return ($snapshot['invoice_hash'] ?? null) === $invoice->snapshot_sha256
                && (int) $event->issued_invoice_id === (int) $invoice->id
                && $event->currency === $invoice->snapshot['currency']
                && preg_match('/^\d{1,16}\.\d{2}$/D', $event->amount)
                && BigDecimal::of($event->amount)->isGreaterThan(0);
        } catch (\Throwable) { return false; }
    }

    public function summary(IssuedInvoice $invoice): array
    {
        if (!app(InvoiceIssuanceService::class)->hasValidSnapshot($invoice)) $this->fail('invoice_invalid');
        $events = InvoicePayment::where('issued_invoice_id', $invoice->id)->orderBy('id')->get();
        $paid = BigDecimal::zero(); $receipts = []; $reversed = [];
        foreach ($events as $event) {
            if (!$this->validEvent($event, $invoice)) $this->fail('ledger_invalid');
            if ($event->kind === 'receipt' && $event->reverses_id === null) {
                if ($event->receipt_reference_hash !== hash('sha256', mb_strtolower(trim($event->reference), 'UTF-8'))) $this->fail('ledger_invalid');
                $receipts[$event->id] = $event;
                $paid = $paid->plus($event->amount);
            } elseif ($event->kind === 'reversal' && isset($receipts[$event->reverses_id]) && !isset($reversed[$event->reverses_id])) {
                $original = $receipts[$event->reverses_id];
                if ($event->receipt_reference_hash !== null) $this->fail('ledger_invalid');
                if ($original->amount !== $event->amount || $original->currency !== $event->currency || !$event->reason) $this->fail('ledger_invalid');
                $reversed[$event->reverses_id] = true;
                $paid = $paid->minus($event->amount);
            } else { $this->fail('ledger_invalid'); }
            if ($paid->isLessThan(0) || $paid->isGreaterThan($invoice->snapshot['amounts']['total'])) $this->fail('ledger_invalid');
        }
        $remaining = BigDecimal::of($invoice->snapshot['amounts']['total'])->minus($paid);
        return [
            'total' => $invoice->snapshot['amounts']['total'], 'paid' => (string) $paid->toScale(2),
            'remaining' => (string) $remaining->toScale(2), 'currency' => $invoice->snapshot['currency'],
            'status' => $remaining->isZero() ? 'paid' : ($paid->isZero() ? 'unpaid' : 'partial'),
            'events' => $events, 'reversed' => $reversed,
        ];
    }

    public function record(IssuedInvoice $invoice, array $input, int $actor): InvoicePayment
    {
        $data = Validator::make($input, [
            'request_key' => ['required', 'uuid'],
            'amount' => ['required', 'string', 'regex:/^\d{1,16}(?:\.\d{1,2})?$/D'],
            'paid_on' => ['required', 'date_format:Y-m-d', 'before_or_equal:'.now('Asia/Dubai')->toDateString()],
            'method' => ['required', 'in:bank_transfer,cash,card,other'],
            'reference' => ['required', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'currency' => ['prohibited'], 'issued_invoice_id' => ['prohibited'],
        ], [], [
            'amount' => __('payments.amount'), 'paid_on' => __('payments.date'),
            'method' => __('payments.method'), 'reference' => __('payments.reference'),
            'notes' => __('payments.notes'), 'reason' => __('payments.reason'),
            'request_key' => __('payments.request_label'), 'currency' => __('payments.currency_label'),
        ])->validate();
        $data['amount'] = (string) BigDecimal::of($data['amount'])->toScale(2);
        if (!BigDecimal::of($data['amount'])->isGreaterThan(0)) $this->fail('positive');
        $data['reference'] = trim($data['reference']);
        if ($data['reference'] === '') $this->fail('reference_required');
        $data['notes'] = $data['notes'] ?? null;
        return $this->commit($invoice, $data, $actor, null);
    }

    public function reverse(IssuedInvoice $invoice, InvoicePayment $payment, array $input, int $actor): InvoicePayment
    {
        $data = Validator::make($input, [
            'request_key' => ['required', 'uuid'], 'reason' => ['required', 'string', 'max:1000'],
        ], [], [
            'amount' => __('payments.amount'), 'paid_on' => __('payments.date'),
            'method' => __('payments.method'), 'reference' => __('payments.reference'),
            'notes' => __('payments.notes'), 'reason' => __('payments.reason'),
            'request_key' => __('payments.request_label'), 'currency' => __('payments.currency_label'),
        ])->validate();
        $data['reason'] = trim($data['reason']);
        if ($data['reason'] === '') $this->fail('reason_required');
        return $this->commit($invoice, $data, $actor, $payment->id);
    }

    private function commit(IssuedInvoice $invoice, array $data, int $actor, ?int $reverseId): InvoicePayment
    {
        $requestHash = $this->digest($data + ['actor' => $actor, 'reverses_id' => $reverseId]);
        try {
            return DB::transaction(function () use ($invoice, $data, $actor, $reverseId, $requestHash): InvoicePayment {
                $locked = IssuedInvoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();
                $summary = $this->summary($locked);
                $existing = InvoicePayment::where('issued_invoice_id', $locked->id)->where('request_key', $data['request_key'])->first();
                if ($existing) {
                    if (!hash_equals($existing->request_hash, $requestHash)) $this->fail('request_conflict');
                    return $existing;
                }
                if ($reverseId !== null) {
                    $original = $summary['events']->firstWhere('id', $reverseId);
                    if (!$original || $original->kind !== 'receipt') $this->fail('wrong_payment');
                    if (isset($summary['reversed'][$reverseId])) $this->fail('already_reversed');
                    $fields = [
                        'kind' => 'reversal', 'reverses_id' => $reverseId, 'amount' => $original->amount,
                        'paid_on' => now('Asia/Dubai')->toDateString(), 'method' => $original->method,
                        'reference' => $original->reference, 'receipt_reference_hash' => null, 'notes' => null, 'reason' => $data['reason'],
                    ];
                } else {
                    if ($summary['events']->contains(fn ($event) => $event->kind === 'receipt' && mb_strtolower(trim($event->reference), 'UTF-8') === mb_strtolower($data['reference'], 'UTF-8'))) $this->fail('duplicate_reference');
                    if ($data['paid_on'] < $locked->snapshot['issue_date']) $this->fail('before_invoice');
                    if (BigDecimal::of($data['amount'])->isGreaterThan($summary['remaining'])) $this->fail('overpayment');
                    $fields = array_intersect_key($data, array_flip(['amount','paid_on','method','reference','notes']))
                        + ['kind' => 'receipt', 'reverses_id' => null, 'reason' => null, 'receipt_reference_hash' => hash('sha256', mb_strtolower($data['reference'], 'UTF-8'))];
                }
                $fields += [
                    'issued_invoice_id' => $locked->id, 'currency' => $locked->snapshot['currency'],
                    'event_uuid' => (string) Str::uuid(), 'recorded_by' => $actor,
                    'recorded_by_name' => User::findOrFail($actor)->name,
                    'recorded_at' => now('UTC')->format('Y-m-d H:i:s'),
                    'request_key' => $data['request_key'], 'request_hash' => $requestHash,
                ];
                $snapshot = $fields + ['invoice_hash' => $locked->snapshot_sha256];
                return InvoicePayment::create($fields + ['snapshot' => $snapshot, 'snapshot_sha256' => $this->digest($snapshot)]);
            }, 3);
        } catch (QueryException $exception) {
            if (!in_array((string) $exception->getCode(), ['23000','23505'], true)) throw $exception;
            // A concurrent retry may have committed the same request.
            $this->summary($invoice->fresh());
            $existing = InvoicePayment::where('issued_invoice_id', $invoice->id)->where('request_key', $data['request_key'])->first();
            if ($existing && hash_equals($existing->request_hash, $requestHash)) return $existing;
            $this->fail('request_conflict');
        }
    }
}
