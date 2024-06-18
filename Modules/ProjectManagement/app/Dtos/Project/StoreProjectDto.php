<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Dtos\Project;

use App\Dtos\BaseDto;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Http\Requests\Project\ProjectRequest;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class StoreProjectDto extends BaseDto
{
    /**
     * @param ProjectDto $projectDto
     * @param DataCollection<int, ObjectiveQuestionDto> $objectiveQuestions
     * @param DataCollection<int, ProjectInputDto> $projectInputs
     * @param DataCollection<int, ProjectOutputDto> $projectOutputs
     * @param int[] $outputLanguages
     * @param User $creator
     */
    public function __construct(
        public ProjectDto $projectDto,
        #[DataCollectionOf(ObjectiveQuestionDto::class)]
        public DataCollection $objectiveQuestions,
        #[DataCollectionOf(ProjectInputDto::class)]
        public DataCollection $projectInputs,
        #[DataCollectionOf(ProjectOutputDto::class)]
        public DataCollection $projectOutputs,
        public array $outputLanguages,
        public User $creator,
    ) {}

    public static function fromProjectRequest(ProjectRequest $request): self
    {
        return self::from(
            $request->validated() + [
                'projectDto' => ProjectDto::from($request->validated()),
                'creator' => $request->user(),
            ]
        );
    }
}
