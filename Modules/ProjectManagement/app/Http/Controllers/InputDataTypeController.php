<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Controllers;

use Modules\ProjectManagement\app\Actions\InputDataType\FetchInputDataTypeListAction;

class InputDataTypeController
{
    public function index(FetchInputDataTypeListAction $action)
    {
        return apiResponse()
            ->success()
            ->data(
                data: $action->execute()
            )
            ->send();
    }
}
