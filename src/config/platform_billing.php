<?php

$mode = env('PLATFORM_BILLING_MODE', 'test');
$live = $mode === 'live';

return [
    'free_launch' => (bool) env('PLATFORM_FREE_LAUNCH', true),
    'mode' => $mode,
    'strict_proof_test' => (bool) env('PLATFORM_BILLING_STRICT_PROOF_TEST_ENABLED', false),
    'enabled' => (bool) ($live ? env('PLATFORM_BILLING_LIVE_ENABLED', false) : env('PLATFORM_BILLING_TEST_ENABLED', false)),
    'checkout_enabled' => (bool) env('PLATFORM_BILLING_LIVE_CHECKOUT_ENABLED', false),
    'secret' => $live ? env('PLATFORM_STRIPE_LIVE_SECRET') : env('PLATFORM_STRIPE_TEST_SECRET'),
    'webhook_secret' => $live ? env('PLATFORM_STRIPE_LIVE_WEBHOOK_SECRET') : env('PLATFORM_STRIPE_TEST_WEBHOOK_SECRET'),
    'api_version' => '2026-10-28.endive',
    'prices' => ['business' => $live ? env('PLATFORM_STRIPE_LIVE_PRICE_BUSINESS') : env('PLATFORM_STRIPE_TEST_PRICE_BUSINESS'), 'professional' => $live ? env('PLATFORM_STRIPE_LIVE_PRICE_PROFESSIONAL') : env('PLATFORM_STRIPE_TEST_PRICE_PROFESSIONAL')],
    'portal_configuration' => $live ? env('PLATFORM_STRIPE_LIVE_PORTAL_CONFIGURATION') : env('PLATFORM_STRIPE_TEST_PORTAL_CONFIGURATION'),
];
