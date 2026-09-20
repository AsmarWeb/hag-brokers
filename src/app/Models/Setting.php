<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'maintenance_mode',
        'phone',
        'email',
    ];

    protected $casts = [
        'maintenance_mode' => 'boolean',
    ];
}
