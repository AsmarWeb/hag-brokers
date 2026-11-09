<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class FreightBookingOffer extends Model
{
    protected $casts = [
        'amount' => 'decimal:2',
        'expires_at' => 'immutable_datetime',
        'accepted_at' => 'immutable_datetime',
        'confirmed_at' => 'immutable_datetime',
    ];
}
