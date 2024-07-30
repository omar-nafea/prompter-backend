<?php

declare(strict_types=1);

return [
    'name' => 'AiServiceManagement',
    'throttle' => [
        'max_attempts' => 1,
        'seconds' => 1 * 60,
    ],
    'log_ai_calls_enabled' => env('LOG_AI_CALLS_ENABLED', false),
    'integrations' => [
        'ai_service_integration' => 'rapid_api',
        'rapid_api' => [
            'ChatGPT3_0' => [
                'name' => 'ChatGPT3_0',
                'base_url' => env('RAPID_API_CHAT_GPT3_0_BASE_URL'),
                'host' => env('RAPID_API_CHAT_GPT3_0_HOST'),
                'api_key' => env('RAPID_API_CHAT_GPT3_0_API_KEY'),
                'class' => Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\ChatGPT3_0::class,
            ],
        ],
    ],
];
