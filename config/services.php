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
    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    ],

    'payment_gateway' => [
        'driver' => env('PAYMENT_GATEWAY_DRIVER', 'midtrans'),
    ],

    'pakasir' => [
        'slug' => env('PAKASIR_PROJECT_SLUG'),
        'api_key' => env('PAKASIR_API_KEY'),
        'qris_only' => env('PAKASIR_QRIS_ONLY', false),
        'redirect_url' => env('PAKASIR_REDIRECT_URL'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'whatsapp' => [
        'provider' => env('WHATSAPP_PROVIDER', 'fonnte'),
        'enabled' => env('WHATSAPP_ENABLED', false),
        'token' => env('WHATSAPP_TOKEN'),
        'endpoint' => env('WHATSAPP_ENDPOINT', 'https://api.fonnte.com/send'),
        'country_code' => env('WHATSAPP_COUNTRY_CODE', '62'),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID'),
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'whatsapp_from' => env('TWILIO_WHATSAPP_FROM'),
    ],

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

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

];
