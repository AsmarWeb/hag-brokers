<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DealDocumentVersion extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'snapshot' => 'array',
        'created_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (): void {
            throw new \LogicException('Deal document versions are immutable.');
        });

        static::deleting(function (): void {
            throw new \LogicException('Deal document versions are retained.');
        });
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(DealDocument::class, 'deal_document_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
