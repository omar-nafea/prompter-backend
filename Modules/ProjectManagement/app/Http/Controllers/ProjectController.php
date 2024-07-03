<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Actions\Project\FetchProjectCodeSnippetsAction;
use Modules\ProjectManagement\app\Actions\Project\FetchProjectListAction;
use Modules\ProjectManagement\app\Actions\Project\FetchSingleProjectAction;
use Modules\ProjectManagement\app\Actions\Project\StoreProjectAction;
use Modules\ProjectManagement\app\Actions\Project\UpdateProjectAction;
use Modules\ProjectManagement\app\Dtos\Project\StoreProjectDto;
use Modules\ProjectManagement\app\Dtos\Project\UpdateProjectDto;
use Modules\ProjectManagement\app\Http\Requests\Project\ProjectRequest;
use Modules\ProjectManagement\app\Http\Resources\ProjectResource;

final class ProjectController
{
    public function index(FetchProjectListAction $action): JsonResponse
    {
        /** @var User $user */
        $user = auth()->user();

        return apiResponse()
            ->success()
            ->data(
                ProjectResource::collection(
                    $action->execute(
                        $user
                    )
                ),
            )->send();
    }

    public function store(
        ProjectRequest $request,
        StoreProjectAction $action,
    ): JsonResponse {

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

    public function update(
        ProjectRequest $request,
        UpdateProjectAction $action,
    ): JsonResponse {
        $action->execute(
            dto: UpdateProjectDto::fromProjectRequest($request)
        );

        return apiResponse()->success()->message('Project updated successfully')->send();
    }

    public function validateProjectFormOnly(Request $request): JsonResponse
    {
        app(ProjectRequest::class);

        if ($request->isMethod('POST')) {
            //            Gate::authorize('store', Listing::class);
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

    public function show(string $project, FetchSingleProjectAction $action): JsonResponse
    {

        return apiResponse()
            ->success()
            ->data(
                data: ProjectResource::make(
                    $action->execute($project)
                )
            )
            ->send();
    }

    public function codeSnippets(string $project, FetchProjectCodeSnippetsAction $action): JsonResponse
    {

        return apiResponse()
            ->success()
            ->data(
                data: $action->execute(
                    $project
                )
            )
            ->send();
    }

    public function destroy(string $project): JsonResponse
    {
        auth()->user()?->projects()->where('key', $project)->firstOrFail()->delete();

        return apiResponse()
            ->success()
            ->message('Project deleted successfully')
            ->send();
    }
}
