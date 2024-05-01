<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Controllers;

use Modules\ProjectManagement\app\Actions\ProjectObjectiveQuestion\FetchProjectObjectiveQuestionListAction;
use Modules\ProjectManagement\app\Http\Resources\ProjectObjectiveQuestionResource;

class ProjectObjectiveQuestionController
{
    public function index(FetchProjectObjectiveQuestionListAction $action)
    {
        return apiResponse()
            ->success()
            ->data(
                data: ProjectObjectiveQuestionResource::collection($action->execute())
            )->send();
    }
}
