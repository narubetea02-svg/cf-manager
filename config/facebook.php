<?php return [
    'app_id' => env('FACEBOOK_CLIENT_ID'),
    'app_secret' => env('FACEBOOK_CLIENT_SECRET'),
    'redirect' => env('FACEBOOK_REDIRECT_URI'),
    'page_token' => env('FACEBOOK_PAGE_TOKEN', ''),
    'verify_token' => env('FACEBOOK_VERIFY_TOKEN', ''),
    'webhook_enabled' => env('FACEBOOK_WEBHOOK_ENABLED', false),
    'send_enabled' => env('MESSENGER_SEND_ENABLED', false),
    'send_test_psid_allowlist' => array_values(array_filter(array_map(
        static fn ($value) => trim($value),
        explode(',', (string) env('MESSENGER_SEND_TEST_PSID_ALLOWLIST', ''))
    ))),
];
