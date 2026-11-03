<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;

class MobileVerifyEmail extends VerifyEmail
{
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute('api.v1.verify', now()->addMinutes(60), [
            'id' => $notifiable->getKey(), 'hash' => sha1($notifiable->getEmailForVerification()),
        ]);
    }
}
