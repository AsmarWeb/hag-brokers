<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyMailPreference extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['enabled' => 'boolean', 'offers' => 'boolean', 'messages' => 'boolean', 'enabled_at' => 'immutable_datetime'];
}
