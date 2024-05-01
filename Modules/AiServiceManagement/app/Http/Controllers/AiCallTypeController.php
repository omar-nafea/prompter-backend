<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Controllers;

use Modules\AiServiceManagement\app\Actions\AiCallType\FetchAiCallTypeListAction;
use Modules\AiServiceManagement\app\Http\Resources\AiCallTypeResource;

class AiCallTypeController
{
    public function index(FetchAiCallTypeListAction $action)
    {
        return apiResponse()
            ->success()
            ->data(
                data: AiCallTypeResource::collection($action->execute())
            )->send();
    }
}
