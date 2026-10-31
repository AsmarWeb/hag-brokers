<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FinalQuotationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $quotationId,
        public string $pdf
    ) {}

    public function build(): static
    {
        return $this
            ->subject('Your Freight Quotation | HAG Brokers')
            ->html(
                '<p>Please find your freight quotation attached.</p>'
                . '<p>Reference ID: '
                . e($this->quotationId)
                . '</p>'
                . '<p>HAG Brokers</p>'
            )
            ->attachData(
                $this->pdf,
                'HAG-Freight-Quotation.pdf',
                ['mime' => 'application/pdf']
            );
    }
}
