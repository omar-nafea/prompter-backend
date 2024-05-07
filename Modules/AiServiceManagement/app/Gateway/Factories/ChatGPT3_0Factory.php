<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Factories;

class ChatGPT3_0Factory
{
    public static function make()
    {
        $aiServiceName = config('ai-service-management.integrations.ai_service_integration');

        //todo add fake chat gpt3_0 implementation
        return app(
            config('ai-service-management.integrations.' . $aiServiceName)['ChatGPT3_0']['class']
        );
    }
}
