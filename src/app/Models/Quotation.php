<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quotation extends Model
{
    protected $casts = ['sailing_snapshot' => 'array'];

    protected $fillable = ['customer_id', 'origin_port_id', 'destination_port_id', 'cargo_type', 'container_type', 'quantity', 'weight', 'preferred_date', 'total_charge', 'status', 'reference_no', 'assigned_to', 'freight_sailing_id', 'sailing_snapshot'];

    public function freightBookingOffer(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(FreightBookingOffer::class);
    }

    public function offers()
    {
        return $this->hasMany(QuotationOffer::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
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
