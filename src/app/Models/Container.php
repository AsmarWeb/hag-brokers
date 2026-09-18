<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Container extends Model
{
    use HasFactory;

    protected $fillable = [
        'shipment_id',
        'container_no',
        'type',
        'size',
        'seal_no',
        'status',
        'tracking_provider',
        'last_tracked_at',
    ];

    protected $casts = [
        'last_tracked_at' => 'datetime',
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }
}
