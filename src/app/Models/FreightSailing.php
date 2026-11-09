<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FreightSailing extends Model
{
    public const CONTAINER_TYPES = ['20ft', '40ft', '40ft_hc'];

    public const STATUSES = ['draft', 'published', 'cancelled'];

    protected $fillable = [
        'origin_port_id', 'destination_port_id', 'carrier_id', 'vessel_id', 'vessel_name',
        'voyage_number', 'departure_at', 'arrival_at', 'cargo_cutoff_at',
        'container_types', 'status',
    ];

    protected $casts = [
        'departure_at' => 'immutable_datetime',
        'arrival_at' => 'immutable_datetime',
        'cargo_cutoff_at' => 'immutable_datetime',
        'container_types' => 'array',
    ];

    public function originPort(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'origin_port_id');
    }

    public function destinationPort(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'destination_port_id');
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }
}
