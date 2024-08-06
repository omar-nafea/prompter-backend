<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Events\Contracts;

use Modules\AiServiceManagement\app\Models\AiCallRequestLog;

interface AiCallEvent
{
    public function log(AiCallRequestLog $log): void;

    /**
     * @return mixed[]
     */
    public function toArray(): array;
}
