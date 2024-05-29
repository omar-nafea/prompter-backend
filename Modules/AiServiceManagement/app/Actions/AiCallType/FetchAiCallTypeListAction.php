<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Actions\AiCallType;

use Illuminate\Support\Collection;
use Modules\AiServiceManagement\app\Models\AiCallType;

class FetchAiCallTypeListAction
{
    public function execute(): Collection
    {
        return AiCallType::query()->get();
    }
}
