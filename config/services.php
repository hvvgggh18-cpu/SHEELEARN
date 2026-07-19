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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL') . '/auth/google/callback'),
    ],

    'twilio' => [
        'sid' => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
        'from' => env('TWILIO_FROM'),
    ],

    'firebase' => [
        // Path to service account JSON or JSON string
        'credentials' => env('FIREBASE_CREDENTIALS'),
    ],

    'groq' => [
        'api_key' => env('GROQ_API_KEY'),
        'base_url' => env('GROQ_API_URL', 'https://api.groq.com/openai/v1'),
        'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
        'vision_model' => env('GROQ_VISION_MODEL', null),
        'max_tokens' => (int) env('GROQ_MAX_TOKENS', 2000),
        'completion_tokens' => (int) env('GROQ_COMPLETION_TOKENS', 1600),
        'context_tokens' => (int) env('GROQ_CONTEXT_TOKENS', 12000),
        'token_safety_margin' => (int) env('GROQ_TOKEN_SAFETY_MARGIN', 50),
        'max_attachment_bytes' => (int) env('GROQ_MAX_ATTACHMENT_BYTES', 4194304),
        'max_document_chars' => (int) env('GROQ_MAX_DOCUMENT_CHARS', 15000),
        'fallback_model' => env('GROQ_FALLBACK_MODEL', 'llama-3.2-70b'),
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
