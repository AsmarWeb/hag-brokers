<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyMarketplaceRequest extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new \LogicException('Usage history is immutable.');
        });
        static::deleting(function (): void {
            throw new \LogicException('Usage history cannot be deleted.');
        });
    }
}
