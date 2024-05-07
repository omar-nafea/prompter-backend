<?php

declare(strict_types=1);

return [
    'name' => 'AiServiceManagement',
    'integrations' => [
        'ai_service_integration' => 'rapid_api',
        'rapid_api' => [
            'ChatGPT3_0' => [
                'name' => 'ChatGPT3_0',
                'base_url' => env('RAPID_API_CHAT_GPT3_0_BASE_URL'),
                'api_key' => env('RAPID_API_CHAT_GPT3_0_API_KEY'),
                'class' => \Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\ChatGPT3_0::class,
            ],
        ],
    ],
];
