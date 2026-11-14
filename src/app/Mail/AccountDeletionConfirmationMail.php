<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class AccountDeletionConfirmationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $confirmationUrl,
        public string $expiresAt,
        public string $language = 'en',
    ) {
        $this->language = in_array($language, ['ar', 'en'], true)
            ? $language
            : 'en';
    }

    public function build(): static
    {
        return $this
            ->subject(
                $this->language === 'ar'
                    ? 'تأكيد طلب حذف حسابك - HAG Brokers'
                    : 'Confirm your account deletion request - HAG Brokers'
            )
            ->view('emails.account-deletion-confirmation');
    }
}
