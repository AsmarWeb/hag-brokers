<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'quotation_id',
        'carrier_id',
        'vessel_id',
        'carrier_amount',
        'customer_amount',
        'currency',
        'departure_date',
        'eta',
        'transit_days',
        'status',
        'expires_at',
        'notes',
    ];

    protected $casts = [
        'carrier_amount' => 'decimal:2',
        'customer_amount' => 'decimal:2',
        'departure_date' => 'date',
        'eta' => 'date',
        'transit_days' => 'integer',
        'expires_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }

    public function vessel(): BelongsTo
    {
        return $this->belongsTo(Vessel::class);
    }
}
