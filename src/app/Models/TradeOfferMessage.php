<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TradeOfferMessage extends Model
{
    protected $guarded = [];

    public $timestamps = false;

    protected $casts = ['created_at' => 'immutable_datetime', 'workspace_id' => 'integer', 'offer_id' => 'integer', 'author_id' => 'integer'];

    protected static function booted(): void
    {
        static::updating(function () {
            throw new \LogicException('Messages cannot be edited.');
        });
        static::deleting(function () {
            throw new \LogicException('Messages cannot be deleted.');
        });
    }
}
