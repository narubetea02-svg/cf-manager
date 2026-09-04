<?php return [
    'enabled' => env('TIKTOK_ENABLED', true),
    'api_key' => env('TIKTOK_API_KEY', ''),
    'webhook_secret' => env('TIKTOK_WEBHOOK_SECRET', ''),
    'grabber_interval' => env('TIKTOK_GRABBER_INTERVAL', 2),
    'allow_mock' => env('TIKTOK_ALLOW_MOCK', false),
];