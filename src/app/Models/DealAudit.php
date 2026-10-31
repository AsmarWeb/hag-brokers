<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealAudit extends Model
{
    public $timestamps = false;
    protected static function booted(): void
    {
        static::updating(function (self $record): void {
            if (true) {
                throw new \LogicException(
                    'Financial record is immutable.'
                );
            }
        });

        static::deleting(function (self $record): void {
            if (true) {
                throw new \LogicException(
                    'Financial record is immutable.'
                );
            }
        });
    }


    protected $fillable = [
        'deal_id',
        'actor_id',
        'event',
        'payload',
        'payload_sha256',
    ];

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }
}
