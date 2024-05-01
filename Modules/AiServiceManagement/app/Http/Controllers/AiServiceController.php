<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Controllers;

use Modules\AiServiceManagement\app\Actions\AiService\FetchAiServiceListAction;
use Modules\AiServiceManagement\app\Http\Resources\AiServiceResource;

class AiServiceController
{
    public function index(FetchAiServiceListAction $action)
    {
        return apiResponse()
            ->success()
            ->data(
                data : AiServiceResource::collection($action->execute())
            )->send();
    }
}
