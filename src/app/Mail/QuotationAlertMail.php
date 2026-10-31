<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class QuotationAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $alertTitle,
        public string $alertMessage,
        public ?string $alertUrl = null,
    ) {}

    public function build(): static
    {
        return $this
            ->subject($this->alertTitle . ' | HAG Brokers')
            ->view('emails.quotation-alert');
    }
}
