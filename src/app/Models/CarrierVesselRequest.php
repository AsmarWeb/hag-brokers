<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CarrierVesselRequest extends Model
{
    protected $fillable = ['carrier_id', 'submitted_by', 'name', 'imo', 'vessel_type'];

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function registeredVessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class, 'imo', 'imo');
    }
}
