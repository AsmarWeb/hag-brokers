<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Laratrust\Contracts\LaratrustUser;
use Laratrust\Traits\HasRolesAndPermissions;

class User extends Authenticatable implements LaratrustUser, MustVerifyEmailContract
{
    use HasFactory, HasRolesAndPermissions, \Laravel\Sanctum\HasApiTokens, MustVerifyEmailTrait, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'customer_id', 'role', 'phone', 'phone_verification_required'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['phone_verification_required' => 'boolean', 'last_login_at' => 'datetime', 'blocked_at' => 'datetime'];
    }

    public function isPlatformAdministrator(): bool
    {
        return $this->hasRole(['admin', 'super-admin']);
    }

    public function hasVerifiedEmail(): bool
    {
        return $this->isPlatformAdministrator() || $this->email_verified_at !== null;
    }

    public function hasVerifiedPhone(): bool
    {
        return $this->isPlatformAdministrator() || $this->phone_verified_at !== null;
    }

    public function hasVerifiedAccount(): bool
    {
        return $this->isPlatformAdministrator()
            || ($this->hasVerifiedEmail()
                && (! $this->phone_verification_required || $this->hasVerifiedPhone()));
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function carrierPartnership(): HasOne
    {
        return $this->hasOne(CarrierInvitation::class)->whereNotNull('accepted_at')->whereNull('revoked_at');
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
