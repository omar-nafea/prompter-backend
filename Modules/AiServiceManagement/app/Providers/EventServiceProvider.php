<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\AiServiceManagement\app\Events\AiCallRequestFailed;
use Modules\AiServiceManagement\app\Events\AiCallRequestPrepared;
use Modules\AiServiceManagement\app\Events\AiCallRequestSent;
use Modules\AiServiceManagement\app\Events\AiCallRequestStarted;
use Modules\AiServiceManagement\app\Listeners\LogAiCall;

final class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        AiCallRequestStarted::class => [
            LogAiCall::class,
        ],
        AiCallRequestPrepared::class => [
            LogAiCall::class,
        ],
        AiCallRequestSent::class => [
            LogAiCall::class,
        ],
        AiCallRequestFailed::class => [
            LogAiCall::class,
        ],
    ];
}
