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
        return self::from(
            $request->validated() + [
                'project' => $request->getProject(),
                'projectDto' => ProjectDto::from($request->validated()),
                'projectDetailsDto' => ProjectDetailsDto::from($request->validated()),
                'authUser' => $request->user(),
            ]
        );
    }
}
