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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // The public map (home page): Mapbox draws it; the Google key is kept for Street View / place search later.
    // Both are browser keys: restrict them to this site's URLs in their dashboards.
    'mapbox' => [
        'token' => env('MAPBOX_TOKEN'),
    ],

    'google' => [
        'maps_key' => env('GOOGLE_MAP_API'),
    ],

    // New enquiries go on WhatsApp to the users who handle them (App\Services\WhatsApp). The token is a system user's
    // permanent token from Meta Business Settings; the phone id is the sender number's id (not the number); "to" is a
    // comma list of extra numbers (the office line), 0812345678 or 66812345678.
    'whatsapp' => [
        'token' => env('WHATSAPP_TOKEN'),
        'phone_id' => env('WHATSAPP_PHONE_ID'),
        'to' => env('WHATSAPP_TO'),
        'template' => env('WHATSAPP_TEMPLATE'),
        'language' => env('WHATSAPP_LANGUAGE', 'en'),
        'version' => env('WHATSAPP_API_VERSION', 'v23.0'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
