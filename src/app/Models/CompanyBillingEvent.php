<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyBillingEvent extends Model
{
    protected $guarded = [];

    protected $casts = ['created_at' => 'immutable_datetime'];

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
