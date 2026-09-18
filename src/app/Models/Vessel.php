<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vessel extends Model
{
    use HasFactory;

    protected $fillable = [
        'carrier_id',
        'name',
        'imo',
        'mmsi',
        'call_sign',
        'flag',
        'vessel_type',
        'latitude',
        'longitude',
        'speed',
        'course',
        'destination',
        'eta',
        'last_position_at',
        'is_in_gulf',
        'status',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'speed' => 'decimal:2',
        'course' => 'decimal:2',
        'eta' => 'datetime',
        'last_position_at' => 'datetime',
        'is_in_gulf' => 'boolean',
    ];

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(VesselPosition::class);
    }

    public function portCalls(): HasMany
    {
        return $this->hasMany(PortCall::class);
    }

    public function quotationOffers(): HasMany
    {
        return $this->hasMany(QuotationOffer::class);
    }

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }
}
