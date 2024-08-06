<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Events;

use Illuminate\Support\Carbon;
use Modules\AiServiceManagement\app\Enums\AiCallRequestStatus;
use Modules\AiServiceManagement\app\Events\Contracts\AiCallEvent;
use Modules\AiServiceManagement\app\Models\AiCallRequestLog;

abstract class BaseAiCallEvent implements AiCallEvent
{
    public AiCallRequestStatus $status;

    public Carbon $eventAt;

    public string $requestUuid;

    public function __construct()
    {
        $this->eventAt = now();
    }

    public function log(AiCallRequestLog $log): void
    {
        $log->update(
            array_merge(
                ['last_status_at' => $this->eventAt],
                $this->toArray(),
            )
        );
    }
}
