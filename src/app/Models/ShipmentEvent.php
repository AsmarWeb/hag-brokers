<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ShipmentEvent extends Model {protected $guarded=[];protected $casts=['event_date'=>'datetime'];public function shipment(){return $this->belongsTo(Shipment::class);}}
