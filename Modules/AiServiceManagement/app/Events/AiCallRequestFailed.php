<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Events;

use Modules\AiServiceManagement\app\Enums\AiCallRequestStatus;

final class AiCallRequestFailed extends BaseAiCallEvent
{
    public AiCallRequestStatus $status = AiCallRequestStatus::Failed;

    public function __construct(
        public string $requestUuid,
        public string $error,
    ) {
        parent::__construct();
    }

    public function toArray(): array
    {
        return [
            'status' => $this->status,
            'error' => $this->error,
        ];
    }
}
