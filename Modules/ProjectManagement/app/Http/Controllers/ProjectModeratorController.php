<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Controllers;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Gate;
use Modules\Auth\app\Http\Resources\UserResource;
use Modules\ProjectManagement\app\Actions\ProjectModerator\CheckProjectModeratorExistenceAction;
use Modules\ProjectManagement\app\Actions\ProjectModerator\DestroyProjectModeratorAction;
use Modules\ProjectManagement\app\Actions\ProjectModerator\FetchProjectModeratorListAction;
use Modules\ProjectManagement\app\Actions\ProjectModerator\InviteProjectModeratorAction;
use Modules\ProjectManagement\app\Dtos\ProjectModerator\CheckProjectModeratorExistenceDto;
use Modules\ProjectManagement\app\Dtos\ProjectModerator\DestroyProjectModeratorDto;
use Modules\ProjectManagement\app\Dtos\ProjectModerator\FetchProjectModeratorListDto;
use Modules\ProjectManagement\app\Dtos\ProjectModerator\InviteProjectModeratorDto;
use Modules\ProjectManagement\app\Http\Requests\ProjectModerator\CheckProjectModeratorExistenceRequest;
use Modules\ProjectManagement\app\Http\Requests\ProjectModerator\DestroyProjectModeratorRequest;
use Modules\ProjectManagement\app\Http\Requests\ProjectModerator\FetchProjectModeratorListRequest;
use Modules\ProjectManagement\app\Http\Requests\ProjectModerator\InviteProjectModeratorRequest;
use Modules\ProjectManagement\app\Models\ProjectModerator;

final class ProjectModeratorController
{
    public function index(
        FetchProjectModeratorListRequest $request,
        FetchProjectModeratorListAction $action,
    ): Responsable {
        return apiResponse()
            ->success()
            ->data(
                data: UserResource::collection(
                    resource: $action->execute(
                        dto: FetchProjectModeratorListDto::fromFetchProjectModeratorListRequest($request)
                    )
                )
            );
    }

    public function checkProjectModeratorExistence(
        CheckProjectModeratorExistenceRequest $request,
        CheckProjectModeratorExistenceAction $action,
    ): Responsable {
        sleep(5);

        $dto = CheckProjectModeratorExistenceDto::fromCheckProjectModeratorExistenceRequest($request);
        Gate::authorize('checkExistence', [ProjectModerator::class, $dto]);

        $moderatorExists = $action->execute(
            dto: $dto
        );

        return apiResponse()
            ->success()
            ->data(
                data: [
                    'exists' => $moderatorExists,
                ]
            )->message(
                $moderatorExists ? 'Project moderator exists' : 'Project moderator does not exist , do you want to create new one and invite him ?'
            );
    }

    public function inviteModerator(
        InviteProjectModeratorRequest $request,
        InviteProjectModeratorAction $action,
    ): Responsable {
        sleep(5);

        $dto = InviteProjectModeratorDto::fromInviteProjectModeratorRequest($request);
        Gate::authorize('invite', [ProjectModerator::class, $dto]);

        $action->execute(
            dto: $dto
        );

        return apiResponse()
            ->success()
            ->message('Project moderator invited successfully');

    }

    public function destroy(
        DestroyProjectModeratorRequest $request,
        DestroyProjectModeratorAction $action,
    ): Responsable {
        $action->execute(
            dto: DestroyProjectModeratorDto::fromDestroyProjectModeratorRequest($request)
        );

        return apiResponse()
            ->success()
            ->message('Project moderator deleted successfully');

    }
}
