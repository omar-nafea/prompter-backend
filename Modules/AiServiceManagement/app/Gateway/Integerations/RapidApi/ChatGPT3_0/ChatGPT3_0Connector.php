<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

class ChatGPT3_0Connector extends Connector
{
    use AlwaysThrowOnErrors;

    public function resolveBaseUrl(): string
    {
        return config('ai-service-management.integrations.rapid_api.ChatGPT3_0.base_url');
    }

    protected function defaultHeaders(): array
    {
        return [
            'X-RapidAPI-Host' => config('ai-service-management.integrations.rapid_api.ChatGPT3_0.base_url'),
            'X-RapidAPI-Key' => config('ai-service-management.integrations.rapid_api.ChatGPT3_0.api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
