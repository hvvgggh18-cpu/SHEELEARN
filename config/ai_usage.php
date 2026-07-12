<?php

return [
    'default_plan' => env('AI_DEFAULT_PLAN', 'free'),
    'reset_interval_hours' => env('AI_RESET_INTERVAL_HOURS', 24),

    'plans' => [
        'guest' => [
            'label' => 'Guest',
            'limit' => env('AI_GUEST_DAILY_LIMIT', 5),
            'reset_interval_hours' => env('AI_GUEST_RESET_INTERVAL_HOURS', null),
        ],

        'free' => [
            'label' => 'Free',
            'limit' => env('AI_FREE_DAILY_LIMIT', 20),
            'reset_interval_hours' => env('AI_RESET_INTERVAL_HOURS', 24),
        ],

        'premium' => [
            'label' => 'Premium',
            'limit' => null,
            'reset_interval_hours' => null,
        ],
    ],
];
