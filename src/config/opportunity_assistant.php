<?php

return [
    'enabled' => env('OPPORTUNITY_AI_ENABLED', false),
    'key' => env('OPPORTUNITY_OPENAI_API_KEY'),
    'model' => env('OPPORTUNITY_OPENAI_MODEL', 'gpt-5-mini'),
    'company_daily_limit' => (int) env('OPPORTUNITY_AI_COMPANY_DAILY_LIMIT', 10),
    'platform_daily_limit' => (int) env('OPPORTUNITY_AI_PLATFORM_DAILY_LIMIT', 100),
];
