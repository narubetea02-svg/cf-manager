<?php return [
    'kerry' => ['api_key' => env('KERRY_API_KEY', ''), 'api_url' => env('KERRY_API_URL', 'https://api.kerryexpress.com/v3')],
    'flash' => ['api_key' => env('FLASH_API_KEY', ''), 'api_url' => env('FLASH_API_URL', 'https://api.flash.co.th')],
    'jnt'   => ['api_key' => env('JNT_API_KEY', ''), 'api_url' => env('JNT_API_URL', 'https://api.jtexpress.co.th')],
    'thai_post' => ['api_key' => env('THAIPOST_API_KEY', ''), 'api_url' => env('THAIPOST_API_URL', 'https://trackapi.thailandpost.co.th')],
    'allow_mock' => env('SHIPPING_ALLOW_MOCK', false),
];