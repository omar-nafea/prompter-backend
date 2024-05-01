<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Actions\AiService;

use Modules\AiServiceManagement\app\Models\AiService;

class FetchAiServiceListAction
{
    public function execute()
    {
        return AiService::where('status', 1)->get();
    }
}
