<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Events;

use Modules\AiServiceManagement\app\Enums\AiCallRequestStatus;
use Str;

final class AiCallRequestPrepared extends BaseAiCallEvent
{
    public AiCallRequestStatus $status = AiCallRequestStatus::Prepared;

    public function __construct(
        public string $requestUuid,
        public string $prompt,
        public string $aiConnector,
        public ?string $integrationService = null,
    ) {
        parent::__construct();
        $this->setIntegrationService();
    }

    public function toArray(): array
    {
        return [
            'prompt' => $this->prompt,
            'status' => $this->status,
            'ai_connector' => $this->aiConnector,
            'integration_service' => $this->integrationService,
        ];
    }

    protected function setIntegrationService(): void
    {
        //todo move this to separate action and cache the integration service => Ai Connector
        $x = collect(config('ai-service-management.integrations'))// @phpstan-ignore-line
            ->filter(
                fn ($item) => is_array($item) && collect($item)->values()
                    ->filter(
                        static fn ($item) => is_array($item)
                    )->count()
            )->flatten(1)
            ->first(
                fn ($item) => Str::contains(
                    $item['class'],// @phpstan-ignore-line
                    str($this->aiConnector)->afterLast('\\')->toString()
                )
            )['name'];
        $this->integrationService = (string) config('ai-service-management.integrations.ai_service_integrations')[$x]; // @phpstan-ignore-line
    }
}
