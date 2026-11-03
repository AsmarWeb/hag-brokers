<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyTradeOfferEvent extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $casts = ['created_at' => 'immutable_datetime'];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \LogicException('Offer history is immutable.');
        });
        static::deleting(function () {
            throw new \LogicException('Offer history cannot be deleted.');
        });
    }
}
