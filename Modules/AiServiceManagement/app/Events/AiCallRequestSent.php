<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Events;

use Modules\AiServiceManagement\app\Enums\AiCallRequestStatus;

final class AiCallRequestSent extends BaseAiCallEvent
{
    public AiCallRequestStatus $status = AiCallRequestStatus::Sent;

    /**
     * @param  array<string,mixed>  $response
     */
    public function __construct(
        public string $requestUuid,
        public array $response,
    ) {
        parent::__construct();
    }

    public function toArray(): array
    {
        return [
            'response' => $this->response,
            'status' => $this->status,
        ];
    }
}
