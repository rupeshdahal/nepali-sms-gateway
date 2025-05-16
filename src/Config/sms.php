<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default SMS Gateway
    |--------------------------------------------------------------------------
    |
    | This option controls the default SMS gateway that is used for sending
    | messages. You may change this default as required but it's recommended
    | that you set up default gateway at the application level.
    |
    | Supported: "sparrow", "akash", "fast"
    |
    */
    'default' => env('SMS_GATEWAY', 'sparrow'),

    /*
    |--------------------------------------------------------------------------
    | SMS Gateway Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all the SMS gateways for your application.
    | Each gateway requires different configuration parameters.
    |
    */
    'gateways' => [
        'sparrow' => [
            'token' => env('SPARROW_SMS_TOKEN'),
            'from' => env('SPARROW_SMS_FROM'),
            'base_url' => env('SPARROW_SMS_BASE_URL', 'http://api.sparrowsms.com/v2/sms/'),
        ],

        'akash' => [
            'auth_key' => env('AKASH_SMS_AUTH_KEY'),
            'sender_id' => env('AKASH_SMS_SENDER_ID'),
            'base_url' => env('AKASH_SMS_BASE_URL', 'https://akashsms.com/api/v3/'),
        ],

        'fast' => [
            'api_key' => env('FAST_SMS_API_KEY'),
            'sender' => env('FAST_SMS_SENDER'),
            'base_url' => env('FAST_SMS_BASE_URL', 'https://fastsms.com/api/v1/'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Log Driver
    |--------------------------------------------------------------------------
    |
    | If you wish to log all sent messages, you can set this to true.
    | This will log all SMS messages using your default Laravel logger.
    |
    */
    'log_enabled' => env('SMS_LOG_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Phone Number Validation
    |--------------------------------------------------------------------------
    |
    | Controls whether phone numbers are validated before sending.
    | If set to true, SMS sending will fail if phone number format is invalid.
    |
    */
    'validate_phone_number' => env('SMS_VALIDATE_PHONE_NUMBER', true),
];
