<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyAccessGrant extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = ['plan_snapshot' => 'array', 'expires_at' => 'immutable_datetime', 'created_at' => 'immutable_datetime'];

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
