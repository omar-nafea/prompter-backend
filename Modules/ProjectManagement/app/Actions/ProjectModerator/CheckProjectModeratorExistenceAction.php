<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\ProjectModerator;

use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Dtos\ProjectModerator\CheckProjectModeratorExistenceDto;
use Modules\ProjectManagement\app\Models\Project;

final class CheckProjectModeratorExistenceAction
{
    public function execute(CheckProjectModeratorExistenceDto $dto): bool
    {
        Project::query()
            ->allowedForUser($dto->authUser)
            ->where('key', $dto->projectId)
            ->firstOrFail();

        return User::query()
            ->allowedForUser($dto->authUser)
            ->where('email', $dto->email)
            ->exists();
    }
}
