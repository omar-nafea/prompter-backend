<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Factories;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;

final class AiGatewayFactory
{
    public function __construct(
        protected Container $app,
    ) {}

    /**
     * @throws BindingResolutionException
     */
    public function make(string $aiModel): mixed
    {
        /** @var string $aiServiceName */
        $aiServiceName = config('ai-service-management.integrations.ai_service_integrations.' . $aiModel);

        //todo add fake chat gpt3_0 implementation

        /** @var array<string,array<string,string>> $aiServiceIntegrationConfig */
        $aiServiceIntegrationConfig = config('ai-service-management.integrations.' . $aiServiceName);

        return $this->app->make($aiServiceIntegrationConfig[$aiModel]['class']);
    }
}
