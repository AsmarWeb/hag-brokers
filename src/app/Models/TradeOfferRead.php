<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeOfferRead extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $casts = ['last_message_id' => 'integer', 'read_at' => 'immutable_datetime'];
}
