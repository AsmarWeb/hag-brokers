<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = ['customer_id', 'shipment_no', 'booking_no', 'origin_port_id', 'destination_port_id', 'vessel_id', 'cargo_type', 'container_type', 'quantity', 'weight', 'status', 'preferred_date', 'eta', 'actual_arrival'];
    protected $casts = ['preferred_date' => 'date', 'eta' => 'date', 'actual_arrival' => 'datetime'];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function containers()
    {
        return $this->hasMany(Container::class);
    }
    public function events()
    {
        return $this->hasMany(ShipmentEvent::class);
    }
    public function documents()
    {
        return $this->hasMany(Document::class);
    }
    public function vessel()
    {
        return $this->belongsTo(Vessel::class);
    }
    public function originPort()
    {
        return $this->belongsTo(Port::class, 'origin_port_id');
    }
    public function destinationPort()
    {
        return $this->belongsTo(Port::class, 'destination_port_id');
    }
}
