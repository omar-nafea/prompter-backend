<?php

declare(strict_types=1);

return [
    'name' => 'AiServiceManagement',
    'characters_per_token_divisor' => env('CHARACTERS_PER_TOKEN_DIVISOR', 4),
    'throttle' => [
        'max_attempts' => 1,
        'seconds' => 1,
    ],
    'log_ai_calls_enabled' => env('LOG_AI_CALLS_ENABLED', false),
    'debug_enabled' => env('AI_DEBUG_ENABLED', false),
    'default_temperature' => env('DEFAULT_TEMPERATURE', 0.9),
    'integrations' => [
        'ai_service_integration' => 'rapid_api',
        'fake_response' => env('AI_SERVICE_FAKE_RESPONSE', false),
        //app working integrations
        //where I can find the specific integration configurations for each module
        'ai_service_integrations' => [
            'ChatGPT3_0' => 'rapid_api',
            'ChatGPT4_0' => 'rapid_api',
            'ChatGPT4_0Turbo' => 'rapid_api',
            'Gemini_1_5_Flash' => 'google',
        ],
        //global integration configurations
        'rapid_api' => [
            'ChatGPT3_0' => [
                'name' => 'ChatGPT3_0',
                'fake_response' => env('RAPID_API_CHAT_GPT3_0_FAKE_RESPONSE', false),
                'base_url' => env('RAPID_API_CHAT_GPT3_0_BASE_URL'),
                'host' => env('RAPID_API_CHAT_GPT3_0_HOST'),
                'api_key' => env('RAPID_API_CHAT_GPT3_0_API_KEY'),
                'class' => Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\ChatGPT3_0::class,
            ],
            'ChatGPT4_0' => [
                'name' => 'ChatGPT4_0',
                'fake_response' => env('RAPID_API_CHAT_GPT4_0_FAKE_RESPONSE', false),
                'base_url' => env('RAPID_API_CHAT_GPT4_0_BASE_URL'),
                'host' => env('RAPID_API_CHAT_GPT4_0_HOST'),
                'api_key' => env('RAPID_API_CHAT_GPT4_0_API_KEY'),
                'class' => Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT4_0\ChatGPT4_0::class,
            ],
            'ChatGPT4_0Turbo' => [
                'name' => 'ChatGPT4_0Turbo',
                'fake_response' => env('RAPID_API_CHAT_CHAT_GPT4_0_TURBO_FAKE_RESPONSE', false),
                'base_url' => env('RAPID_API_CHAT_CHAT_GPT4_0_TURBO_BASE_URL'),
                'host' => env('RAPID_API_CHAT_CHAT_GPT4_0_TURBO_HOST'),
                'api_key' => env('RAPID_API_CHAT_CHAT_GPT4_0_TURBO_API_KEY'),
                'class' => Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT4_0Turbo\ChatGPT4_0Turbo::class,
            ],
        ],
        'google' => [
            'Gemini_1_5_Flash' => [
                'name' => 'Gemini_1_5_Flash',
                'fake_response' => env('GOGGLE_GEMINI_1_5_FLASH_FAKE_RESPONSE', false),
                'base_url' => env('GOGGLE_GEMINI_1_5_FLASH_BASE_URL'),
                'api_client' => env('GOGGLE_GEMINI_1_5_FLASH_API_CLIENT'),
                'api_key' => env('GOGGLE_GEMINI_1_5_FLASH_API_KEY'),
                'class' => Modules\AiServiceManagement\app\Gateway\Integerations\Google\GeminiFlash1_5\GeminiFlash1_5::class,
            ],
        ],
    ],
];
