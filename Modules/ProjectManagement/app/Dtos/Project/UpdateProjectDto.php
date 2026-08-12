<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Dtos\Project;

use App\Dtos\BaseDto;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Http\Requests\Project\ProjectRequest;
use Modules\ProjectManagement\app\Models\Project;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class UpdateProjectDto extends BaseDto
{
    /**
     * @param  DataCollection<int, ObjectiveQuestionDto>  $objectiveQuestions
     * @param  DataCollection<int, ProjectInputDto>  $projectInputs
     * @param  DataCollection<int, ProjectOutputDto>  $projectOutputs
     * @param  int[]  $outputLanguages
     */
    public function __construct(
        public Project $project,
        public ProjectDto $projectDto,
        public ProjectDetailsDto $projectDetailsDto,
        public ?ProjectAiModelDto $projectAiModelDto,
        #[DataCollectionOf(ObjectiveQuestionDto::class)]
        public DataCollection $objectiveQuestions,
        #[DataCollectionOf(ProjectInputDto::class)]
        public DataCollection $projectInputs,
        #[DataCollectionOf(ProjectOutputDto::class)]
        public DataCollection $projectOutputs,
        public array $outputLanguages,
        public User $authUser,
    ) {}

    public static function fromProjectRequest(ProjectRequest $request): self
    {
        $validated = $request->validated();

        return self::from(
            $validated + [
                'project' => $request->getProject(),
                'projectDto' => ProjectDto::from($validated),
                'projectDetailsDto' => ProjectDetailsDto::from($validated),
                'projectAiModelDto' => filled($validated['ai_model_name'] ?? null)
                    ? ProjectAiModelDto::from([
                        'name' => $validated['ai_model_name'],
                        'alias' => $validated['ai_model_alias'],
                        'provider' => $validated['ai_model_provider'],
                        'api_key' => $validated['ai_model_api_key'] ?? null,
                        'connector_url' => $validated['ai_model_connector_url'] ?? null,
                    ])
                    : null,
                'authUser' => $request->user(),
            ]
        );
    }
}
