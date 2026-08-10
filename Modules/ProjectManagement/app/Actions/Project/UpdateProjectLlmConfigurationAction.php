<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\Project;

use Modules\ProjectManagement\app\Models\Project;

final class UpdateProjectLlmConfigurationAction
{
    /**
     * @param  array{system_prompt: ?string, temperature: float|int, max_output_tokens: int, response_schema: ?array<string, mixed>}  $configuration
     */
    public function execute(Project $project, array $configuration): Project
    {
        $project->details()->updateOrCreate([], [
            'system_prompt' => $configuration['system_prompt'],
            'ai_temperature' => $configuration['temperature'],
            'max_output_tokens' => $configuration['max_output_tokens'],
            'response_schema' => $configuration['response_schema'],
        ]);

        return $project->load('details');
    }
}
