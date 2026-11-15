<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

final class CompanyWorkspaceClosureVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public string $verificationUrl, public string $companyName, public string $language = 'en')
    {
        $this->language = in_array($language, ['ar', 'en'], true) ? $language : 'en';
    }

    public function build(): static
    {
        return $this
            ->subject($this->language === 'ar' ? 'تأكيد طلب إغلاق مساحة الشركة - HAG Brokers' : 'Verify your HAG Brokers company workspace closure request')
            ->view('emails.company-workspace-closure-verification');
    }
}
