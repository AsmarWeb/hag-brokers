<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class FreightRateSource extends Model
{
    protected $fillable = [
        'code', 'name', 'type', 'tier', 'markup_percent', 'markup_fixed', 'capabilities', 'credentials', 'is_active',
        'last_checked_at', 'health_status', 'health_message',
    ];

    protected $hidden = ['credentials'];

    protected function casts(): array
    {
        return [
            'capabilities' => 'array',
            'credentials' => 'encrypted:array',
            'is_active' => 'boolean',
            'markup_percent' => 'decimal:3',
            'markup_fixed' => 'decimal:2',
            'last_checked_at' => 'immutable_datetime',
        ];
    }

    public function rates(): HasMany
    {
        return $this->hasMany(FreightRate::class, 'source_id');
    }
}
