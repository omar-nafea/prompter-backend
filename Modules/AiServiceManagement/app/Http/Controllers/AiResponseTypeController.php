<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Controllers;

use Modules\AiServiceManagement\app\Actions\AiResponseType\FetchAiResponseTypeListAction;
use Modules\AiServiceManagement\app\Http\Resources\AiResponseTypeResource;

class AiResponseTypeController
{
    public function index(FetchAiResponseTypeListAction $action)
    {
        return apiResponse()
            ->success()
            ->data(
                data : AiResponseTypeResource::collection($action->execute())
            )->send();
    }
}
