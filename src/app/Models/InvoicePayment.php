<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    public $timestamps = false;
    protected $guarded = [];
    protected $casts = ['snapshot' => 'array', 'paid_on' => 'date', 'recorded_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(function (): void { throw new \LogicException('Payment events cannot be changed.'); });
        static::deleting(function (): void { throw new \LogicException('Payment events cannot be deleted.'); });
    }
}
