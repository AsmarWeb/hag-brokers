<?php

return [
    // Explicitly opt in during local Mailpit development only.
    'enabled' => env('HAG_FINAL_QUOTATION_DELIVERY_ENABLED', false),
];
