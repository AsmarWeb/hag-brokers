<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'vessel_id',
        'port_id',
        'arrival_at',
        'departure_at',
        'status',
    ];

    protected $casts = [
        'arrival_at' => 'datetime',
        'departure_at' => 'datetime',
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

    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class);
    }
}
