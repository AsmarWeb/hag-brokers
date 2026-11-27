<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DealDocument extends Model
{
    protected $guarded = [];

    protected $casts = [
        'content' => 'array',
        'financial_snapshot' => 'array',
        'locked_at' => 'datetime',
        'prepared_at' => 'datetime',
        'signed_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (self $record): void {
            if ($record->getRawOriginal('locked_at') !== null && $record->isDirty([
                'deal_id',
                'reference',
                'type',
                'title',
                'language',
                'content',
                'financial_snapshot',
                'financial_snapshot_sha256',
                'document_sha256',
                'template_version',
            ])) {
                throw new \LogicException('Prepared deal documents are immutable.');
            }
        });

        static::deleting(function (): void {
            throw new \LogicException('Deal documents are retained as commercial records.');
        });
    }

    public function deal(): BelongsTo
    {
        return $this->belongsTo(Deal::class);
    }

    public function parties(): HasMany
    {
        return $this->hasMany(DealDocumentParty::class)->orderBy('signing_order');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DealDocumentVersion::class)->orderByDesc('version');
    }

    public function events(): HasMany
    {
        return $this->hasMany(DealDocumentEvent::class)->orderByDesc('created_at');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
