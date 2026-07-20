<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway;

use Illuminate\Contracts\Container\Container;
use Modules\AiServiceManagement\app\Enums\AiModelProvider;
use Modules\AiServiceManagement\app\Gateway\Contracts\AiProviderConnector;
use Modules\AiServiceManagement\app\Gateway\Providers\AnthropicConnector;
use Modules\AiServiceManagement\app\Gateway\Providers\GeminiConnector;
use Modules\AiServiceManagement\app\Gateway\Providers\OpenAiConnector;

final class AiProviderResolver
{
    public function __construct(
        protected Container $app,
    ) {}

    public function for(AiModelProvider $provider): AiProviderConnector
    {
        return match ($provider) {
            AiModelProvider::OpenAi,
            AiModelProvider::OpenRouter,
            AiModelProvider::OpenAiCompatible => $this->app->make(OpenAiConnector::class),
            AiModelProvider::Gemini => $this->app->make(GeminiConnector::class),
            AiModelProvider::Anthropic => $this->app->make(AnthropicConnector::class),
        };
    }
}
