<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Controllers;

use Modules\ProjectManagement\app\Actions\StoreProjectAction;
use Modules\ProjectManagement\app\Dtos\Project\StoreProjectDto;
use Modules\ProjectManagement\app\Http\Requests\Project\ProjectRequest;
use Modules\ProjectManagement\app\Http\Resources\ProjectResource;

final class ProjectController
{
    public function store(
        ProjectRequest $request,
        StoreProjectAction $action,
    ) {

        return apiResponse()
            ->success()
            ->message('Successfully created a new project')
            ->data(
                ProjectResource::make(
                    $action->execute(
                        dto: StoreProjectDto::fromProjectRequest($request)
                    )
                ),
            )->send();
    }
}
