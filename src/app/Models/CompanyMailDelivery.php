<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyMailDelivery extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['next_attempt_at' => 'immutable_datetime', 'sent_at' => 'immutable_datetime', 'queued_at' => 'immutable_datetime'];
}
