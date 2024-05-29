<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Actions\AiResponseType;

use Modules\AiServiceManagement\app\Models\AiResponseType;

class FetchAiResponseTypeListAction
{
    public function execute()
    {
        return AiResponseType::query()->get();
    }
}
