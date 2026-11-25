<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MarineVesselListing extends Model
{
    public const STATUSES = ['pending_review', 'published', 'rejected', 'closed'];
    public const VESSEL_TYPES = ['general_cargo', 'bulk', 'tanker', 'lct', 'ro_ro', 'container', 'other'];

    protected $fillable = [
        'public_reference', 'user_id', 'company_name', 'contact_name', 'email', 'phone',
        'vessel_type', 'dwt_capacity', 'current_port_id', 'current_position', 'open_date',
        'preferred_trading_area', 'status', 'reviewed_by', 'review_notes', 'published_at',
        'closed_at', 'terms_accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'dwt_capacity' => 'decimal:2',
            'open_date' => 'date',
            'published_at' => 'immutable_datetime',
            'closed_at' => 'immutable_datetime',
            'terms_accepted_at' => 'immutable_datetime',
        ];
    }

    public function currentPort(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'current_port_id');
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
