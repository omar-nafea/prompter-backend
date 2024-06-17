<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Controllers;

use Modules\ProjectManagement\app\Actions\InputDataType\FetchInputDataTypeListAction;
use Modules\ProjectManagement\app\Http\Resources\InputDataTypeResource;

final class InputDataTypeController
{
    public function index(FetchInputDataTypeListAction $action)
    {
        return apiResponse()
            ->success()
            ->data(
                data: InputDataTypeResource::collection($action->execute())
            )
            ->send();
    }
}
