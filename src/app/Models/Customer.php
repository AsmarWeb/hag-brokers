<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Customer extends Model {protected $guarded=[];public function user(){return $this->hasOne(User::class);}public function shipments(){return $this->hasMany(Shipment::class);}public function quotations(){return $this->hasMany(Quotation::class);}}
