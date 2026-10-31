<?php

namespace App\Services;

use App\Models\User;
use App\Mail\QuotationAlertMail;
use App\Notifications\AdminPlatformNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /**
     * Send a notification to all users with
     * Laratrust admin or super-admin roles.
     */
    public static function admins(
        string $type,
        string $title,
        string $message,
        ?Model $model = null,
        array $data = []
    ): void {
        $admins = User::query()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', [
                    'admin',
                    'super-admin',
                ]);
            })
            ->get();

        foreach ($admins as $admin) {
            self::send(
                user: $admin,
                type: $type,
                title: $title,
                message: $message,
                model: $model,
                data: $data
            );
        }
    }

    /**
     * Send a notification to one specific user.
     */
    public static function user(
        User $user,
        string $type,
        string $title,
        string $message,
        ?Model $model = null,
        array $data = []
    ): void {
        self::send(
            user: $user,
            type: $type,
            title: $title,
            message: $message,
            model: $model,
            data: $data
        );
    }

    /**
     * Actually create and send the database notification.
     */
    protected static function send(
        User $user,
        string $type,
        string $title,
        string $message,
        ?Model $model = null,
        array $data = []
    ): void {
        $url = $data['url'] ?? null;

        $extra = array_merge([
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->getKey(),
        ], $data);

        $user->notify(
            new AdminPlatformNotification(
                type: $type,
                title: $title,
                message: $message,
                url: $url,
                extra: $extra,
            )
        );

        if (
            config('quotation_alerts.email_enabled', false)
            && str_starts_with($type, 'quotation.')
            && filter_var($user->email, FILTER_VALIDATE_EMAIL)
        ) {
            try {
                Mail::to($user->email)->queue(
                    new QuotationAlertMail($title, $message, $url)
                );
            } catch (\Throwable $exception) {
                report($exception);
            }
        }
    }
}
