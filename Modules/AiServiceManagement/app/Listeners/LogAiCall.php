<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Listeners;

use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Modules\AiServiceManagement\app\Events\BaseAiCallEvent;
use Modules\AiServiceManagement\app\Models\AiCallRequestLog;

final class LogAiCall implements ShouldHandleEventsAfterCommit, ShouldQueue
{
    use InteractsWithQueue;

    public function handle(BaseAiCallEvent $event): void
    {
        if ( ! config('log_ai_calls_enabled')) {
            return;
        }
        $event->log(
            AiCallRequestLog::firstOrCreate([
                'request_uuid' => $event->requestUuid,
            ])
        );
    }
}
