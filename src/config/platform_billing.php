<?php

return [
    'enabled' => (bool) env('PLATFORM_BILLING_TEST_ENABLED', false),
    'secret' => env('PLATFORM_STRIPE_TEST_SECRET'),
    'webhook_secret' => env('PLATFORM_STRIPE_TEST_WEBHOOK_SECRET'),
    'api_version' => '2026-10-28.endive',
    'prices' => ['business' => env('PLATFORM_STRIPE_TEST_PRICE_BUSINESS'), 'professional' => env('PLATFORM_STRIPE_TEST_PRICE_PROFESSIONAL')],
    'portal_configuration' => env('PLATFORM_STRIPE_TEST_PORTAL_CONFIGURATION'),
];
