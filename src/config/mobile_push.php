<?php

return [
    'enabled' => (bool) env('MOBILE_PUSH_ENABLED', false),
    'project_id' => env('MOBILE_PUSH_PROJECT_ID'),
    'access_token' => env('MOBILE_PUSH_ACCESS_TOKEN'),
];
