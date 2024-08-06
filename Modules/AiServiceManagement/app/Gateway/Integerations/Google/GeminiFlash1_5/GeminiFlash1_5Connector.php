<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\Google\GeminiFlash1_5;

use Override;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AlwaysThrowOnErrors;

final class GeminiFlash1_5Connector extends Connector
{
    use AlwaysThrowOnErrors;

    #[Override]
    public function resolveBaseUrl(): string
    {
        /** @var string */
        return config('ai-service-management.integrations.google.Gemini_1_5_Flash.base_url');
    }

    #[Override]
    protected function defaultHeaders(): array
    {
        return [
            'X-Goog-Api-Client' => config('ai-service-management.integrations.google.Gemini_1_5_Flash.api_client'),
            'X-Goog-Api-Key' => config('ai-service-management.integrations.google.Gemini_1_5_Flash.api_key'),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
