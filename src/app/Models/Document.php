<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Document extends Model
{
    protected $guarded = [];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)
            ->orderByDesc('version');
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getVersionAttribute(): int
    {
        if ($this->relationLoaded('versions')) {
            return (int) ($this->versions->max('version') ?? 1);
        }

        return (int) ($this->versions()->max('version') ?? 1);
    }

    public function getDiskAttribute(): string
    {
        return 'local';
    }

}
