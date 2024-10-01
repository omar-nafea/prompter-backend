<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\Project;

use Modules\ProjectManagement\app\Models\Project;

final class FetchSingleProjectAction
{
    public function execute(string $projectKey): Project
    {
        return Project::query()
            //->allowedForUser($dto->user) todo implement and use this scope
            ->with([
                'inputs.enumValues',
                'outputs.enumValues',
                'answers.objectiveQuestion',
                'aiService',
                'aiCallType',
                'aiResponseType',
                'outputLanguages',
                'details',
            ])
            ->where('key', $projectKey)
            ->firstOrFail();
    }
}
