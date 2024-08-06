<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Observers;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Modules\AiServiceManagement\app\DataObjects\AiCallRequestLog\AiCallStatusLogDataObject;
use Modules\AiServiceManagement\app\Models\AiCallRequestLog;

final class AiCallRequestLogObserver implements ShouldHandleEventsAfterCommit
{
    public function updating(AiCallRequestLog $log): void
    {
        if ($log->isDirty('status')) {
            $log->status_log->toCollection()->push(
                new AiCallStatusLogDataObject(
                    $log->status,
                    $log->last_status_at // @phpstan-ignore-line
                )
            );
        }
    }
}
