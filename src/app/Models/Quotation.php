<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'origin_port_id',
        'destination_port_id',
        'cargo_type',
        'container_type',
        'quantity',
        'weight',
        'preferred_date',
        'currency',
        'base_freight',
        'origin_charges',
        'destination_charges',
        'documentation_fee',
        'other_charges',
        'broker_margin',
        'customer_total',
        'total_charge',
        'status',
        'reference_no',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'weight' => 'decimal:2',
        'preferred_date' => 'date',
        'base_freight' => 'decimal:2',
        'origin_charges' => 'decimal:2',
        'destination_charges' => 'decimal:2',
        'documentation_fee' => 'decimal:2',
        'other_charges' => 'decimal:2',
        'broker_margin' => 'decimal:2',
        'customer_total' => 'decimal:2',
        'total_charge' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function originPort(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'origin_port_id');
    }

    public function destinationPort(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'destination_port_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(QuotationOffer::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
