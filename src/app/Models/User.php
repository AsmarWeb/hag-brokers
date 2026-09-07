<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $fillable = ['name', 'email', 'password', 'customer_id', 'role', 'phone'];
    protected $hidden = ['password', 'remember_token'];
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
