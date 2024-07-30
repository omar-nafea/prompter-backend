<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Events;

use Modules\AiServiceManagement\app\Enums\AiCallRequestStatus;

final class AiCallRequestStarted extends BaseAiCallEvent
{
    public AiCallRequestStatus $status = AiCallRequestStatus::Started;

    /**
     * @param  array<string,mixed>  $requestBody
     */
    public function __construct(
        public string $requestUuid,
        public array $requestBody,
        public string $aiServiceName,
        public int $projectId,
        public string $integrationService,
    ) {
        parent::__construct();
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'request_body' => $this->requestBody,
            'ai_service_name' => $this->aiServiceName,
            'project_id' => $this->projectId,
            'integration_service' => $this->integrationService,
        ];
    }
}
