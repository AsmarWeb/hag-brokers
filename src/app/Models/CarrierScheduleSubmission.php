<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CarrierScheduleSubmission extends Model
{
    protected $fillable = ['carrier_id', 'submitted_by', 'rows', 'plan', 'revision'];

    protected $casts = ['rows' => 'array', 'plan' => 'array'];

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }
}
