<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'codeforces' => [
        'api_base' => env('CODEFORCES_API_BASE', 'https://codeforces.com/api'),
        'cache_ttl' => env('CODEFORCES_CACHE_TTL', 600), // 10 minutes default
        'rate_limit_delay' => env('CODEFORCES_RATE_LIMIT_DELAY', 2), // seconds between requests
        'timeout' => env('CODEFORCES_TIMEOUT', 10), // HTTP timeout in seconds
    ],

];
