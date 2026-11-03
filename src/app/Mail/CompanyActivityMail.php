<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;

class CompanyActivityMail extends Mailable
{
    public function __construct(public string $eventType, public int $offerId, public string $language) {}

    public function build()
    {
        $language = in_array($this->language, ['ar', 'en'], true) ? $this->language : 'en';
        $url = rtrim(config('app.url'), '/').'/company/trade-offers/'.$this->offerId.($this->eventType === 'rfq.message' ? '/room' : '').'?lang='.$language;

        return $this->subject(trans('company_mail.subjects.'.$this->eventType, [], $language))->view('mail.company-activity', compact('language', 'url'));
    }
}
