<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformPlan extends Model
{
    protected $guarded = [];

    protected $casts = ['monthly_amount_minor' => 'integer', 'seats' => 'integer', 'active_opportunities' => 'integer', 'monthly_applications' => 'integer', 'is_available' => 'boolean'];
}
