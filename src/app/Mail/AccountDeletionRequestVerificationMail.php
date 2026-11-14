<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class AccountDeletionRequestVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $verificationUrl,
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
                    ? 'تأكيد طلب حذف حساب HAG Brokers'
                    : 'Verify your HAG Brokers account deletion request'
            )
            ->view('emails.account-deletion-request-verification');
    }
}
