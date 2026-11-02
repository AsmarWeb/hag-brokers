<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IssuedInvoice extends Model
{
    protected $guarded = [];
    protected $casts = [
        'snapshot' => 'array', 'issued_at' => 'datetime', 'supply_date' => 'date',
        'subtotal' => 'decimal:2', 'tax_amount' => 'decimal:2', 'total' => 'decimal:2',
        'subtotal_aed' => 'decimal:2', 'tax_aed' => 'decimal:2', 'total_aed' => 'decimal:2',
        'exchange_rate' => 'decimal:8',
    ];

    protected static function booted(): void
    {
        static::updating(function (): void { throw new \LogicException('Issued invoices are immutable.'); });
        static::deleting(function (): void { throw new \LogicException('Issued invoices cannot be deleted.'); });
    }

    public function draft(): BelongsTo { return $this->belongsTo(InvoiceDraft::class, 'invoice_draft_id'); }
}
