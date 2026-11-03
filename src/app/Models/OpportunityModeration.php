<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpportunityModeration extends Model
{
    protected $guarded = [];

    protected $casts = ['hidden' => 'boolean', 'version' => 'integer'];
}
