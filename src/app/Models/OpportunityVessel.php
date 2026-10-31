<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpportunityVessel extends Model
{
    protected $fillable = [
        'opportunity_id', 'listing_type', 'vessel_category',
        'vessel_type', 'vessel_name', 'builder', 'year_built',
        'length_m', 'beam_m', 'draft_m', 'gross_tonnage',
        'deadweight_tonnage', 'imo_number', 'flag_state',
        'classification_society', 'cabins', 'guest_capacity',
        'crew_capacity', 'engine_make', 'engine_hours',
        'condition', 'price_on_request',
    ];

    protected $casts = [
        'year_built' => 'integer',
        'length_m' => 'decimal:2',
        'beam_m' => 'decimal:2',
        'draft_m' => 'decimal:2',
        'gross_tonnage' => 'decimal:2',
        'deadweight_tonnage' => 'decimal:2',
        'cabins' => 'integer',
        'guest_capacity' => 'integer',
        'crew_capacity' => 'integer',
        'engine_hours' => 'integer',
        'price_on_request' => 'boolean',
    ];

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }
}
