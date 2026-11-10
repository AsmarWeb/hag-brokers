<?php

$domains = explode(',', (string) env(
    'DISPOSABLE_EMAIL_DOMAINS',
    'mailinator.com,guerrillamail.com,guerrillamailblock.com,sharklasers.com,yopmail.com,10minutemail.com,temp-mail.org,tempmail.com,getnada.com,maildrop.cc,dispostable.com,trashmail.com,emailondeck.com,mohmal.com,throwawaymail.com,fakeinbox.com,mintemail.com,mailcatch.com,inboxkitten.com,mailnesia.com,discard.email,spamgourmet.com,mytemp.email'
));

return [
    // Keep this list configurable: disposable-mail domains change frequently.
    'disposable_email_domains' => array_values(array_unique(array_filter(array_map(
        static fn (string $domain): string => strtolower(trim($domain)),
        $domains
    )))),
];
