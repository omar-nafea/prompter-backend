<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\AiServiceManagement\app\Actions\AiCallType\FetchAiCallTypeListAction;
use Modules\AiServiceManagement\app\Http\Resources\AiCallTypeResource;

final class AiCallTypeController
{
    public function index(FetchAiCallTypeListAction $action): JsonResponse
    {
        return apiResponse()
            ->success()
            ->data(
                data: AiCallTypeResource::collection($action->execute())
            )->send();
    }
}
