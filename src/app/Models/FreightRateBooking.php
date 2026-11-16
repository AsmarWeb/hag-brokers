<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class FreightRateBooking extends Model
{
    protected $fillable = [
        'quotation_id', 'customer_id', 'rate_id', 'source_id', 'carrier_id', 'request_key',
        'payload_hash', 'status', 'currency', 'supplier_total', 'customer_total', 'commission_total',
        'rate_snapshot', 'carrier_reference', 'status_note', 'submitted_at', 'confirmed_at', 'rejected_at',
    ];

    protected $hidden = ['supplier_total', 'commission_total', 'payload_hash'];

    protected function casts(): array
    {
        return [
            'supplier_total' => 'decimal:2',
            'customer_total' => 'decimal:2',
            'commission_total' => 'decimal:2',
            'rate_snapshot' => 'array',
            'submitted_at' => 'immutable_datetime',
            'confirmed_at' => 'immutable_datetime',
            'rejected_at' => 'immutable_datetime',
        ];
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function rate(): BelongsTo
    {
        return $this->belongsTo(FreightRate::class, 'rate_id');
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(FreightRateSource::class, 'source_id');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(FreightRateBookingEvent::class, 'freight_rate_booking_id')->latest('id');
    }
}
