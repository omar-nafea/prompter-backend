<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\AiServiceManagement\app\Actions\AiService\FetchAiServiceListAction;
use Modules\AiServiceManagement\app\Http\Resources\AiServiceResource;

final class AiServiceController
{
    public function index(FetchAiServiceListAction $action): JsonResponse
    {
        return apiResponse()
            ->success()
            ->data(
                data : AiServiceResource::collection($action->execute())
            )->send();
    }
}
