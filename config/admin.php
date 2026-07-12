<?php

return [
    'platform_name' => env('ADMIN_PLATFORM_NAME', 'SHEELEARN'),
    'support_email' => env('ADMIN_SUPPORT_EMAIL', 'dasinagee2@gmail.com'),
    'default_model' => env('ADMIN_DEFAULT_MODEL', 'GPT-4o'),
    'max_tokens' => env('ADMIN_MAX_TOKENS', 4096),
    'temperature' => env('ADMIN_TEMPERATURE', 0.7),
    'maintenance_mode' => env('ADMIN_MAINTENANCE_MODE', false),
    'allow_registrations' => env('ADMIN_ALLOW_REGISTRATIONS', true),
    'two_factor_auth' => env('ADMIN_TWO_FACTOR_AUTH', false),
    'session_timeout' => env('ADMIN_SESSION_TIMEOUT', true),
    'session_duration' => env('ADMIN_SESSION_DURATION', 120),
    'max_login_attempts' => env('ADMIN_MAX_LOGIN_ATTEMPTS', 5),
];
