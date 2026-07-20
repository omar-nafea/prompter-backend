<?php

declare(strict_types=1);

return [
    'name' => 'AiServiceManagement',
    'throttle' => [
        'max_attempts' => 50,
        'seconds' => 1,
    ],
    'log_ai_calls_enabled' => env('LOG_AI_CALLS_ENABLED', false),
    'debug_enabled' => env('AI_DEBUG_ENABLED', false),
];
