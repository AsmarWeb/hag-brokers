<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MarineCargoListing extends Model
{
    public const STATUSES = ['pending_review', 'published', 'rejected', 'closed'];
    public const CARGO_TYPES = ['dry_bulk', 'breakbulk', 'project', 'container', 'liquid', 'petroleum', 'general', 'other'];

    protected $fillable = [
        'public_reference', 'user_id', 'company_name', 'contact_name', 'email', 'phone',
        'cargo_type', 'description', 'public_description', 'quantity', 'loading_port_id',
        'discharging_port_id', 'laycan_from', 'laycan_to', 'readiness_date', 'status',
        'reviewed_by', 'review_notes', 'published_at', 'closed_at', 'terms_accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:3',
            'laycan_from' => 'date',
            'laycan_to' => 'date',
            'readiness_date' => 'date',
            'published_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
            'terms_accepted_at' => 'immutable_datetime',
        ];
    }

    public function loadingPort(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'loading_port_id');
    }

    public function dischargingPort(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'discharging_port_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
