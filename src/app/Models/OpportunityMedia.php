<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpportunityMedia extends Model
{
    use HasFactory;

    protected $fillable = [
        'opportunity_id',
        'type',
        'path',
        'mime_type',
        'size',
        'original_name',
        'sort_order',
        'is_primary',
    ];

    protected $casts = [
        'size' => 'integer',
        'sort_order' => 'integer',
        'is_primary' => 'boolean',
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(
            Opportunity::class
        );
    }
}
