<?php
return [
    'indexing_enabled' => env('SEO_INDEXING_ENABLED', env('APP_ENV') === 'production'),
    'canonical_url' => env('SEO_CANONICAL_URL'),
    'social_image' => env('SEO_SOCIAL_IMAGE', ''),
    'google_verification' => env('SEO_GOOGLE_SITE_VERIFICATION', ''),
    'bing_verification' => env('SEO_BING_SITE_VERIFICATION', ''),
    'sitemap_page_size' => 1000,
];
