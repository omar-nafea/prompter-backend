<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Dtos\Project;

use App\Dtos\BaseDto;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Http\Requests\Project\ProjectRequest;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

class StoreProjectDto extends BaseDto
{
    public function __construct(
        public ProjectDto $projectDto,
        #[DataCollectionOf(ObjectiveQuestionDto::class)]
        public DataCollection $objectiveQuestions,
        #[DataCollectionOf(ProjectInputDto::class)]
        public DataCollection $projectInputs,
        #[DataCollectionOf(ProjectOutputDto::class)]
        public DataCollection $projectOutputs,
        #[DataCollectionOf('int')]
        public DataCollection $outputLanguages, //todo specify int type
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
