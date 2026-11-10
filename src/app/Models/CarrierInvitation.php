<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CarrierInvitation extends Model
{
    protected $fillable = ['carrier_id', 'email', 'token_hash', 'expires_at'];

    protected $hidden = ['token_hash'];

    protected $casts = [
        'expires_at' => 'immutable_datetime',
        'accepted_at' => 'immutable_datetime',
        'revoked_at' => 'immutable_datetime',
    ];

    public function status(): string
    {
        return match (true) {
            $this->revoked_at !== null => 'revoked',
            $this->accepted_at !== null => $this->user_id ? 'accepted' : 'closed',
            $this->expires_at->lte(now()) => 'expired',
            default => 'pending',
        };
    }

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }
}
