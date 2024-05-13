<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\Project;

use Modules\ProjectManagement\app\Models\Project;

class FetchSingleProjectAction
{
    public function execute(string $projectKey)
    {
        return Project::query()
            //->allowedForUser($dto->user) todo implement and use this scope
            ->with([
                'inputs',
                'outputs',
                'answers.objectiveQuestion',
                'aiService',
                'aiCallType',
                'aiResponseType',
            ])
            ->where('key', $projectKey)->firstOrFail();
    }
}
