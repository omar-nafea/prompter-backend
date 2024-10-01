<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\Project;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Models\Project;

final class FetchProjectListAction
{
    /**
     * @return LengthAwarePaginator<Project>
     */
    public function execute(User $user): LengthAwarePaginator
    {
        return Project::allowedForUser($user)
            ->with([
                'inputs',
                'outputs',
                'aiService',
                'aiCallType',
                'aiResponseType',
                'details',
            ])->latest()
            ->paginate(request()->integer('per_page'));
    }
}
