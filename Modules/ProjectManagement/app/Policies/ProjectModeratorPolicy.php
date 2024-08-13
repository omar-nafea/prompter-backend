<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Policies;

use Illuminate\Auth\Access\Response;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Dtos\ProjectModerator\CheckProjectModeratorExistenceDto;
use Modules\ProjectManagement\app\Dtos\ProjectModerator\InviteProjectModeratorDto;
use Modules\ProjectManagement\app\Models\Project;
use Modules\ProjectManagement\app\Models\ProjectModerator;

final class ProjectModeratorPolicy
{
    const PROJECT_MODERATOR_EXISTS_CAN_NOT_INVITE_YOURSELF_EXCEPTION_CODE = '7b5a0516-f77d-40b6-bc59-922cff1d6dae';

    const PROJECT_MODERATOR_INVITE_CAN_NOT_INVITE_YOURSELF_EXCEPTION_CODE = '77fe383b-f814-4b27-9e5f-ddb648fc17c4';

    const PROJECT_MODERATOR_ALREADY_EXISTS_EXCEPTION_CODE = '11199a11-2eac-4618-b1c8-1359f19bf705';

    public function checkExistence(User $user, CheckProjectModeratorExistenceDto $dto): bool|Response
    {
        if ($dto->email->toNative() === $user->email->toNative()) {
            return Response::deny(
                'You can not add or invite yourself as a moderator.',
                self::PROJECT_MODERATOR_EXISTS_CAN_NOT_INVITE_YOURSELF_EXCEPTION_CODE
            );
        }

        $project = Project::query()->allowedForUser($user)
            ->where('key', $dto->projectId)
            ->firstOrFail();
        if (ProjectModerator::allowedForUser($user)
            ->where('project_id', $project->id)
            ->whereRelation('user', 'email', $dto->email)
            ->exists()) {
            return Response::deny(
                'Moderator already exists in the project.',
                self::PROJECT_MODERATOR_ALREADY_EXISTS_EXCEPTION_CODE
            );
        }

        return true;
    }

    public function invite(User $user, InviteProjectModeratorDto $dto): bool|Response
    {
        if ($dto->email->toNative() === $user->email->toNative()) {
            return Response::deny(
                'You can not add or invite yourself as a moderator.',
                self::PROJECT_MODERATOR_INVITE_CAN_NOT_INVITE_YOURSELF_EXCEPTION_CODE
            );
        }

        $project = Project::query()->allowedForUser($user)
            ->where('key', $dto->projectId)
            ->firstOrFail();
        if (ProjectModerator::allowedForUser($user)
            ->where('project_id', $project->id)
            ->whereRelation('user', 'email', $dto->email)
            ->exists()) {
            return Response::deny(
                'Moderator already exists in the project.',
                self::PROJECT_MODERATOR_ALREADY_EXISTS_EXCEPTION_CODE
            );
        }

        return true;
    }
}
