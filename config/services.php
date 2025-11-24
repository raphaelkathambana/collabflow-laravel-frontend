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

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],
    'python' => [
        'url' => env('PYTHON_SERVICE_URL', 'http://localhost:8001'),
        'timeout' => env('PYTHON_SERVICE_TIMEOUT', 30),
        'enabled' => env('PYTHON_SERVICE_ENABLED', true),
    ],

    'n8n' => [
        'webhook_url' => env('N8N_WEBHOOK_URL', 'https://n8n.collabflow-n8n.cloud/webhook/project/orchestration'),
        'timeout' => env('N8N_TIMEOUT', 10),
    ],

];
