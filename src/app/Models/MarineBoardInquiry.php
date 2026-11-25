<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MarineBoardInquiry extends Model
{
    protected $fillable = [
        'public_reference', 'cargo_listing_id', 'vessel_listing_id', 'user_id',
        'name', 'company_name', 'email', 'phone', 'message', 'status', 'terms_accepted_at',
    ];

    protected function casts(): array
    {
        return ['terms_accepted_at' => 'immutable_datetime'];
    }

    public function cargoListing(): BelongsTo
    {
        return $this->belongsTo(MarineCargoListing::class, 'cargo_listing_id');
    }

    public function vesselListing(): BelongsTo
    {
        return $this->belongsTo(MarineVesselListing::class, 'vessel_listing_id');
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
