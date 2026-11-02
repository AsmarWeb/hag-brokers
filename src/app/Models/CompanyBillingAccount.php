<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyBillingAccount extends Model
{
    protected $guarded = [];

    protected $casts = ['plan_snapshot' => 'array', 'checkout_expires_at' => 'immutable_datetime', 'paid_until' => 'immutable_datetime', 'last_synced_at' => 'immutable_datetime', 'cancel_at_period_end' => 'boolean'];
}
