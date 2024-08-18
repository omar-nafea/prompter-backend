<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\ProjectModerator;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\ProjectManagement\app\Dtos\ProjectModerator\FetchProjectModeratorListDto;
use Modules\ProjectManagement\app\Models\Project;

final class FetchProjectModeratorListAction
{
    public function execute(FetchProjectModeratorListDto $dto): LengthAwarePaginator
    {
        return Project::query()
            ->allowedForUser($dto->authUser)
            ->where('key', $dto->projectId)
            ->firstOrFail()
            ->moderators()
            ->allowedForUser($dto->authUser)
            ->paginate(
                page: $dto->page,
                perPage: $dto->perPage,
            );
    }
}
