<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;

class User extends Authenticatable implements LaratrustUser
{
    use HasFactory, HasRolesAndPermissions, \Laravel\Sanctum\HasApiTokens, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'customer_id', 'role', 'phone'];

    protected $hidden = ['password', 'remember_token'];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    protected static function booted(): void
    {
        static::updating(function (User $user): void {
            if ($user->isDirty('customer_id') && Schema::hasTable('company_members') && CompanyMember::where('user_id', $user->id)->exists()) {
                throw ValidationException::withMessages(['customer_id' => __('platform.member_reassignment')]);
            }
        });
        static::deleting(function (User $user): void {
            if (Schema::hasTable('company_members') && CompanyMember::where('user_id', $user->id)->where('role', 'owner')->exists()) {
                throw ValidationException::withMessages(['user' => __('platform.owner_protected')]);
            }
        });
    }
}
