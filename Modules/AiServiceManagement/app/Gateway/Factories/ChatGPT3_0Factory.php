<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Factories;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\ChatGPT3_0 as ChatGPT3_0Contract;

final class ChatGPT3_0Factory
{
    public function __construct(
        protected Container $app,
    ) {}

    /**
     * @throws BindingResolutionException
     */
    public function make(): ChatGPT3_0Contract
    {
        /** @var string $aiServiceName */
        $aiServiceName = config('ai-service-management.integrations.ai_service_integration');

        //todo add fake chat gpt3_0 implementation

        /** @var array<string,array<string,string>> $aiServiceIntegrationConfig */
        $aiServiceIntegrationConfig = config('ai-service-management.integrations.' . $aiServiceName);

        /** @var ChatGPT3_0Contract */
        return $this->app->make($aiServiceIntegrationConfig['ChatGPT3_0']['class']);
    }
}
