<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceDraft extends Model
{
    protected $guarded = [];

    protected $attributes = ['status' => 'draft'];

    protected $casts = ['amount' => 'decimal:2', 'snapshot' => 'array', 'due_date' => 'date'];

    protected static function booted(): void
    {
        static::updating(function (self $draft): void {
            if ($draft->getRawOriginal('status') !== 'draft' || $draft->isDirty([
                'reference', 'deal_commission_id', 'deal_id', 'deal_agreement_id', 'customer_id',
                'status', 'currency', 'amount', 'source_agreement_sha256', 'snapshot', 'snapshot_sha256', 'created_by',
            ])) {
                throw new \LogicException('Invoice draft financial snapshot is immutable.');
            }
        });
        static::deleting(function (): void {
            throw new \LogicException('Invoice draft records cannot be deleted.');
        });
    }

    public function deal(): BelongsTo { return $this->belongsTo(Deal::class); }
    public function commission(): BelongsTo { return $this->belongsTo(DealCommission::class, 'deal_commission_id'); }
    public function agreement(): BelongsTo { return $this->belongsTo(DealAgreement::class, 'deal_agreement_id'); }
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
}
