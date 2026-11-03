<?php

return [
    'enabled' => (bool) env('COMPANY_MAIL_ENABLED', false),
    'mailer' => env('COMPANY_MAIL_MAILER', 'smtp'),
];
