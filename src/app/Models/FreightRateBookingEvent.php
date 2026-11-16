<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class FreightRateBookingEvent extends Model
{
    protected $fillable = [
        'actor_id',
        'event_key',
        'from_status',
        'to_status',
        'note',
        'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(FreightRateBooking::class, 'freight_rate_booking_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
