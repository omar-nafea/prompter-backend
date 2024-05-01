<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Actions\AiCallType;

use Modules\AiServiceManagement\app\Models\AiCallType;

class FetchAiCallTypeListAction
{
    public function execute()
    {
        return AiCallType::where('status', 1)->get();
    }
}
