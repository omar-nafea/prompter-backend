<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\ProjectModerator;

use Modules\ProjectManagement\app\Dtos\ProjectModerator\DestroyProjectModeratorDto;
use Modules\ProjectManagement\app\Models\Project;
use Modules\ProjectManagement\app\Models\ProjectModerator;

final class DestroyProjectModeratorAction
{
    public function execute(DestroyProjectModeratorDto $dto): void
    {
        $project = Project::query()
            ->allowedForUser($dto->authUser)
            ->where('key', $dto->projectId)
            ->firstOrFail();
        ProjectModerator::allowedForUser($dto->authUser)
            ->where('user_id', $dto->moderatorId)
            ->where('project_id', $project->id)
            ->firstOrFail()
            ->delete();

    }
}
