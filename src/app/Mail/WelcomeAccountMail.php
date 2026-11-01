<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class WelcomeAccountMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName
    ) {}

    public function build(): static
    {
        return $this
            ->subject('Welcome to HAG Brokers | مرحباً بك')
            ->view('emails.welcome-account');
    }
}
