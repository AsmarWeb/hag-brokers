<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyBillingReceipt extends Model
{
    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        $this->setTable(config('platform_billing.mode', 'test') === 'live' ? 'company_live_billing_receipts' : 'company_billing_receipts');
        parent::__construct($attributes);
    }

    protected $casts = ['plan_snapshot' => 'array', 'paid_until' => 'immutable_datetime', 'amount_paid_minor' => 'integer', 'created_at' => 'immutable_datetime'];

    public $timestamps = false;

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new \LogicException('Billing history is immutable.');
        });
        static::deleting(function (): void {
            throw new \LogicException('Billing history cannot be deleted.');
        });
    }
}
