<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Controllers;

use Illuminate\Auth\Access\Gate;
use Illuminate\Http\Request;
use Modules\ProjectManagement\app\Actions\Project\FetchProjectListAction;
use Modules\ProjectManagement\app\Actions\StoreProjectAction;
use Modules\ProjectManagement\app\Dtos\Project\StoreProjectDto;
use Modules\ProjectManagement\app\Http\Requests\Project\ProjectRequest;
use Modules\ProjectManagement\app\Http\Resources\ProjectResource;

final class ProjectController
{
    public function index(FetchProjectListAction $action)
    {
        return apiResponse()
            ->success()
            ->data(
                ProjectResource::collection(
                    $action->execute(
                        auth()->user()
                    )
                ),
            )->send();
    }

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

    public function validateProjectFormOnly(Request $request)
    {

        if ($request->isMethod('POST')) {
            //            Gate::authorize('store', Listing::class);
            app(ProjectRequest::class);
        }
        //            Gate::authorize('update', [Listing::class, $request->route('listing')->id]);
        //            app(ListingUpdateRequest::class);

        return apiResponse()->success()
            ->data(
                data: [
                    'valid' => true,
                ]
            )
            ->send();
    }
}
