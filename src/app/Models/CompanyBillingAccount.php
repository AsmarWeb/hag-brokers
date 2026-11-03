<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyBillingAccount extends Model
{
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        $this->setTable(config('platform_billing.mode', 'test') === 'live' ? 'company_live_billing_accounts' : 'company_billing_accounts');
        parent::__construct($attributes);
    }

    protected $casts = ['plan_snapshot' => 'array', 'checkout_expires_at' => 'immutable_datetime', 'paid_until' => 'immutable_datetime', 'last_synced_at' => 'immutable_datetime', 'cancel_at_period_end' => 'boolean'];
}
