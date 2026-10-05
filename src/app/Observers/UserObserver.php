<?php

namespace App\Observers;

use App\Models\User;
use App\Services\NotificationService;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        NotificationService::admins(
            type: 'user.created',
            title: 'New User Registered',
            message: sprintf(
                '%s has registered a new account.',
                $user->name ?: $user->email
            ),
            model: $user,
            data: [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'user_email' => $user->email,
                'url' => route(
                    'admin.users.index'
                ),
            ]
        );
    }
}
