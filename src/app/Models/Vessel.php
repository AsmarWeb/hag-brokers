<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vessel extends Model
{
    protected $guarded = [];

    public function carrier(): BelongsTo
    {
        return $this->belongsTo(Carrier::class);
    }
}
