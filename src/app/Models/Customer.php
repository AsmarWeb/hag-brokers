<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Customer extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Customer Users
    |--------------------------------------------------------------------------
    */

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Backward compatibility
    |--------------------------------------------------------------------------
    |
    | إذا كان لديك كود قديم يستخدم $customer->user
    |
    */

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Shipments
    |--------------------------------------------------------------------------
    */

    public function shipments(): HasMany
    {
        return $this->hasMany(Shipment::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Quotations
    |--------------------------------------------------------------------------
    */

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    /*
    |--------------------------------------------------------------------------
    | CRM Contacts
    |--------------------------------------------------------------------------
    */

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    /*
    |--------------------------------------------------------------------------
    | CRM Activities
    |--------------------------------------------------------------------------
    */

    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /*
    |--------------------------------------------------------------------------
    | CRM Tasks
    |--------------------------------------------------------------------------
    */

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /*
    |--------------------------------------------------------------------------
    | CRM Opportunities
    |--------------------------------------------------------------------------
    */

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class);
    }
}
