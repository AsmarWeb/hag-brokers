<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vessel extends Model
{
    protected $guarded = [];

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
