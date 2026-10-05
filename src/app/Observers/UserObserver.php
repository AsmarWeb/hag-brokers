<?php

namespace App\Observers;

use App\Models\User;
use App\Notifications\AdminPlatformNotification;
use App\Services\NotificationService;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        /*
         * =====================================================
         * ADMIN NOTIFICATION
         * =====================================================
         */
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

        /*
         * =====================================================
         * USER WELCOME NOTIFICATION
         * =====================================================
         */
        $user->notify(
            new AdminPlatformNotification(
                type: 'welcome',
                title: 'Welcome to HAG Brokers 👋',
                message: sprintf(
                    'Welcome %s! Your HAG Brokers account has been created successfully.',
                    $user->name ?: 'to HAG Brokers'
                ),
                url: route('dashboard'),
                extra: [
                    'user_id' => $user->id,
                    'action' => 'complete_profile',
                ]
            )
        );
    }
}
