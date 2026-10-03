<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [

        // =====================================================
        // CRM Relationships
        // =====================================================

        'customer_id',
        'contact_id',
        'assigned_to',

        // =====================================================
        // Basic Opportunity Information
        // =====================================================

        'title',
        'opportunity_type',
        'category',
        'description',

        // =====================================================
        // Freight / Cargo
        // =====================================================

        'cargo_type',
        'container_type',
        'origin_port_id',
        'destination_port_id',

        // =====================================================
        // Location
        // =====================================================

        'country',
        'city',

        // =====================================================
        // Financial Information
        // =====================================================

        'estimated_value',
        'required_capital',
        'min_investment',
        'currency',

        // =====================================================
        // Commission
        // =====================================================

        'commission_type',
        'commission_rate',

        // =====================================================
        // CRM Pipeline
        // =====================================================

        'stage',
        'probability',

        // =====================================================
        // Marketplace
        // =====================================================

        'visibility',
        'verification_status',

        // =====================================================
        // Dates
        // =====================================================

        'expected_close_date',
        'published_at',
        'expires_at',

        // =====================================================
        // Notes
        // =====================================================

        'notes',
    ];


    protected $casts = [

        // Financial
        'estimated_value' => 'decimal:2',
        'required_capital' => 'decimal:2',
        'min_investment' => 'decimal:2',
        'commission_rate' => 'decimal:2',

        // CRM
        'probability' => 'integer',

        // Dates
        'expected_close_date' => 'date',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];


    // =========================================================
    // CRM Relationships
    // =========================================================

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }


    // =========================================================
    // Port Relationships
    // =========================================================

    public function originPort(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'origin_port_id');
    }

    public function destinationPort(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'destination_port_id');
    }


    // =========================================================
    // Marketplace Relationships
    // =========================================================

    public function interests(): HasMany
    {
        return $this->hasMany(OpportunityInterest::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(
            OpportunityMedia::class
        )->orderBy('sort_order');
    }
}
