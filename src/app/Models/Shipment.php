<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shipment extends Model
{
    protected $fillable = [
        'customer_id', 'quotation_id', 'shipment_no', 'booking_no',
        'origin_port_id', 'destination_port_id', 'carrier_id', 'vessel_id',
        'cargo_type', 'container_type', 'quantity', 'weight', 'status',
        'preferred_date', 'etd', 'eta', 'actual_departure', 'actual_arrival',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'weight' => 'decimal:2',
        'preferred_date' => 'date',
        'etd' => 'datetime',
        'eta' => 'datetime',
        'actual_departure' => 'datetime',
        'actual_arrival' => 'datetime',
    ];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function quotation(): BelongsTo { return $this->belongsTo(Quotation::class); }
    public function carrier(): BelongsTo { return $this->belongsTo(Carrier::class); }
    public function vessel(): BelongsTo { return $this->belongsTo(Vessel::class); }
    public function originPort(): BelongsTo { return $this->belongsTo(Port::class, 'origin_port_id'); }
    public function destinationPort(): BelongsTo { return $this->belongsTo(Port::class, 'destination_port_id'); }
    public function containers(): HasMany { return $this->hasMany(Container::class); }
    public function events(): HasMany { return $this->hasMany(ShipmentEvent::class); }
    public function documents(): HasMany { return $this->hasMany(Document::class); }
}
