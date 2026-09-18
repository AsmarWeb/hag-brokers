<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Port extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'country',
        'unlocode',
        'country_code',
        'port_code',
        'timezone',
        'latitude',
        'longitude',
        'is_gulf',
        'is_uae',
        'is_active',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'is_gulf' => 'boolean',
        'is_uae' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function originQuotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'origin_port_id');
    }

    public function destinationQuotations(): HasMany
    {
        return $this->hasMany(Quotation::class, 'destination_port_id');
    }

    public function originShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'origin_port_id');
    }

    public function destinationShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'destination_port_id');
    }

    public function portCalls(): HasMany
    {
        return $this->hasMany(PortCall::class);
    }

    public function shipmentEvents(): HasMany
    {
        return $this->hasMany(ShipmentEvent::class);
    }
}
