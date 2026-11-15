<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class CompanyWorkspaceClosureRequest extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'token_expires_at' => 'immutable_datetime',
            'email_sent_at' => 'immutable_datetime',
            'email_verified_at' => 'immutable_datetime',
            'owner_confirmed_at' => 'immutable_datetime',
            'reviewed_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'previous_customer_active' => 'boolean',
        ];
    }
}
