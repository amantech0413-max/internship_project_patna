<?php

return [
    'api_version' => env('WHATSAPP_API_VERSION', 'v22.0'),
    'base_url' => env('WHATSAPP_API_URL', 'https://graph.facebook.com'),
    'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    'access_token' => env('WHATSAPP_ACCESS_TOKEN', env('WHATSAPP_TOKEN')),
    'default_country_code' => env('WHATSAPP_DEFAULT_COUNTRY_CODE', '91'),
    'enabled' => env('WHATSAPP_ENABLED', false),
    'max_retries' => (int) env('WHATSAPP_MAX_RETRIES', 3),
    'retry_delay_seconds' => (int) env('WHATSAPP_RETRY_DELAY_SECONDS', 120),
];
