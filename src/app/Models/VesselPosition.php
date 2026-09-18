<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VesselPosition extends Model
{
    use HasFactory;

    protected $fillable = [
        'vessel_id',
        'latitude',
        'longitude',
        'speed',
        'course',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'speed' => 'decimal:2',
        'course' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}
