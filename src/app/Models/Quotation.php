<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Quotation extends Model {protected $fillable=['customer_id','origin_port_id','destination_port_id','cargo_type','container_type','quantity','weight','preferred_date','total_charge','status','reference_no'];public function customer(){return $this->belongsTo(Customer::class);}public function originPort(){return $this->belongsTo(Port::class,'origin_port_id');}public function destinationPort(){return $this->belongsTo(Port::class,'destination_port_id');}}
