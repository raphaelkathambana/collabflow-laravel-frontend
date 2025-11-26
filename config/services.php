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
        'base_url' => env('N8N_BASE_URL', 'https://n8n.collabflow-n8n.cloud'),
        'webhooks' => [
            'project_start' => env('N8N_PROJECT_START_WEBHOOK', '/webhook/collabflow-project-start'),
            'multi_task' => env('N8N_MULTI_TASK_WEBHOOK', '/webhook/collabflow-multi-task'),
            'task_status' => env('N8N_TASK_STATUS_WEBHOOK', '/webhook/collabflow-task-status'),
            'hitl_start' => env('N8N_HITL_START_WEBHOOK', '/webhook/collabflow-hitl-start'),
            'document_upload' => env('N8N_DOCUMENT_UPLOAD_WEBHOOK', '/webhook/collabflow-document-upload'),
            'notification' => env('N8N_NOTIFICATION_WEBHOOK', '/webhook/collabflow-notification'),
        ],
        // Legacy webhook_url for backwards compatibility (maps to multi_task)
        'webhook_url' => env('N8N_BASE_URL', 'https://n8n.collabflow-n8n.cloud') . env('N8N_MULTI_TASK_WEBHOOK', '/webhook/collabflow-multi-task'),
        'timeout' => env('N8N_TIMEOUT', 10),
        'max_retries' => env('N8N_MAX_RETRIES', 3),
        'retry_delay' => env('N8N_RETRY_DELAY', 2),
    ],

];
