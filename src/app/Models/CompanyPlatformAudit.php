<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyPlatformAudit extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['details' => 'array', 'created_at' => 'immutable_datetime'];

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new \LogicException('Platform history is immutable.');
        });
        static::deleting(function (): void {
            throw new \LogicException('Platform history cannot be deleted.');
        });
    }
}
