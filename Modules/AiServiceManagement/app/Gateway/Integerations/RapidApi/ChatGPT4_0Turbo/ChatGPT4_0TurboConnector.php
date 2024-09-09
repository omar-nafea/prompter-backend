<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT4_0Turbo;

use Override;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

final class ChatGPT4_0TurboConnector extends Connector
{
    use AlwaysThrowOnErrors;

    #[Override]
    public function resolveBaseUrl(): string
    {
        /** @var string */
        return config('ai-service-management.integrations.rapid_api.ChatGPT4_0Turbo.base_url');
    }

    #[Override]
    protected function defaultHeaders(): array
    {
        return [
            'X-RapidAPI-Host' => config('ai-service-management.integrations.rapid_api.ChatGPT4_0Turbo.host'),
            'X-RapidAPI-Key' => config('ai-service-management.integrations.rapid_api.ChatGPT4_0Turbo.api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
