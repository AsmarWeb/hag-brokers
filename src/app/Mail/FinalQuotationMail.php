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
        string $pdf
    ) {
        $this->pdfBase64 = base64_encode($pdf);
    }

    public string $pdfBase64;

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
                base64_decode($this->pdfBase64, true),
                'HAG-Freight-Quotation.pdf',
                ['mime' => 'application/pdf']
            );
    }
}
