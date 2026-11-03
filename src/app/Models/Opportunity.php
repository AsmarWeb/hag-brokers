<?php

namespace App\Models;

use App\Services\Seo\OpportunitySlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Validation\ValidationException;

class Opportunity extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_workspace_id', 'submitted_by', 'marketplace_state',

        // =====================================================
        // CRM Relationships
        // =====================================================

        'customer_id',
        'contact_id',
        'assigned_to',

        // =====================================================
        // Basic Opportunity Information
        // =====================================================

        'commercial_details',
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
        'commercial_details' => 'array',
        'seo_updated_at' => 'immutable_datetime',

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

    public function moderation(): HasOne
    {
        return $this->hasOne(OpportunityModeration::class);
    }

    public function moderationEvents(): HasMany
    {
        return $this->hasMany(OpportunityModerationEvent::class);
    }

    public function isLuxuryMarineListing(): bool
    {
        return in_array($this->opportunity_type, ['sell', 'vessel_brokerage'], true)
            && ($this->opportunity_type === 'vessel_brokerage' || $this->category === 'marine')
            && $this->vessel && $this->vessel->listing_type === 'sale'
            && in_array($this->vessel->vessel_category, ['yacht', 'boat'], true);
    }

    protected static function booted(): void
    {
        static::creating(function (Opportunity $opportunity): void {
            $opportunity->slug = OpportunitySlug::make($opportunity->title);
            $opportunity->seo_updated_at = now('UTC');
        });
        static::updating(function (Opportunity $opportunity): void {
            if ($opportunity->isDirty('slug')) {
                throw ValidationException::withMessages(['slug' => 'Published URL identifiers cannot be changed.']);
            }
            if ($opportunity->isDirty(['title', 'description', 'opportunity_type', 'category', 'commercial_details', 'country', 'city', 'currency', 'estimated_value', 'required_capital', 'min_investment', 'commission_rate', 'visibility', 'verification_status', 'published_at', 'expires_at', 'marketplace_state'])) {
                $opportunity->seo_updated_at = now('UTC');
            }
        });
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        // Only public routes opt into slug binding. Admin/company routes retain IDs.
        if ($field === 'slug' && ctype_digit((string) $value)) {
            return parent::resolveRouteBindingQuery($query, $value, 'id');
        }

        return parent::resolveRouteBindingQuery($query, $value, $field);
    }

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

    public function vessel(): HasOne
    {
        return $this->hasOne(OpportunityVessel::class);
    }
}
