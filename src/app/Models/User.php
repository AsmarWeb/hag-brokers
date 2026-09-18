<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;

class User extends Authenticatable implements LaratrustUser
{
    use HasFactory, Notifiable, HasRolesAndPermissions;
    protected $fillable = ['name', 'email', 'password', 'customer_id', 'role', 'phone'];
    protected $hidden = ['password', 'remember_token'];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
