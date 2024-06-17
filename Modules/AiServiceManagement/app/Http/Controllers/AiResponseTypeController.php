<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Controllers;

use Illuminate\Http\Response;
use Modules\AiServiceManagement\app\Actions\AiResponseType\FetchAiResponseTypeListAction;
use Modules\AiServiceManagement\app\Http\Resources\AiResponseTypeResource;

final class AiResponseTypeController
{
    public function index(FetchAiResponseTypeListAction $action): Response
    {
        return apiResponse()
            ->success()
            ->data(
                data : AiResponseTypeResource::collection($action->execute())
            )->send();
    }
}
