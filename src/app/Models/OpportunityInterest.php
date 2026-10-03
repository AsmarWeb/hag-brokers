<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpportunityInterest extends Model
{
    use HasFactory;

    protected $fillable = [

        // =====================================================
        // Relationships
        // =====================================================

        'opportunity_id',
        'user_id',

        // =====================================================
        // Interest Information
        // =====================================================

        'role',
        'available_capital',
        'currency',
        'message',

        // =====================================================
        // Status
        // =====================================================

        'status',

        // =====================================================
        // Review
        // =====================================================

        'reviewed_at',
        'reviewed_by',
    ];


    protected $casts = [

        'available_capital' => 'decimal:2',

        'reviewed_at' => 'datetime',
    ];


    // =========================================================
    // Opportunity
    // =========================================================

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }


    // =========================================================
    // User who submitted the interest
    // =========================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }


    // =========================================================
    // HAG employee/admin who reviewed the interest
    // =========================================================

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
