<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FreightRate extends Model
{
    public const CONTAINER_TYPES = ['20ft', '40ft', '40ft_hc'];

    protected $fillable = [
        'source_id', 'carrier_id', 'freight_sailing_id', 'origin_port_id', 'destination_port_id',
        'container_type', 'min_quantity', 'max_quantity', 'supplier_unit_cost', 'customer_unit_price', 'markup_percent', 'markup_fixed',
        'currency', 'surcharge_items', 'included_charges', 'excluded_charges', 'availability',
        'booking_mode', 'rate_type', 'status', 'source_reference', 'quoted_at', 'valid_from',
        'expires_at', 'terms',
    ];

    protected function casts(): array
    {
        return [
            'supplier_unit_cost' => 'decimal:2',
            'customer_unit_price' => 'decimal:2',
            'markup_percent' => 'decimal:3',
            'markup_fixed' => 'decimal:2',
            'surcharge_items' => 'array',
            'included_charges' => 'array',
            'excluded_charges' => 'array',
            'quoted_at' => 'immutable_datetime',
            'valid_from' => 'immutable_datetime',
            'expires_at' => 'immutable_datetime',
        ];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(FreightRateSource::class, 'source_id');
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function sailing(): BelongsTo
    {
        return $this->belongsTo(FreightSailing::class, 'freight_sailing_id');
    }

    public function originPort(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'origin_port_id');
    }

    public function destinationPort(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'destination_port_id');
    }
}
