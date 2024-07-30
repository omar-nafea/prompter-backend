<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Events;

use Modules\AiServiceManagement\app\Enums\AiCallRequestStatus;

final class AiCallRequestPrepared extends BaseAiCallEvent
{
    public AiCallRequestStatus $status = AiCallRequestStatus::Prepared;

    public function __construct(
        public string $requestUuid,
        public string $prompt,
        public string $aiConnector,
    ) {
        parent::__construct();
    }

    public function toArray(): array
    {
        return [
            'prompt' => $this->prompt,
            'status' => $this->status,
            'ai_connector' => $this->aiConnector,
        ];
    }
}
