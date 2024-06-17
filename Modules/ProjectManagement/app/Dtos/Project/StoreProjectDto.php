<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Dtos\Project;

use App\Dtos\BaseDto;
use Illuminate\Support\Collection;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Http\Requests\Project\ProjectRequest;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\DataCollection;

final class StoreProjectDto extends BaseDto
{
    public function __construct(
        public ProjectDto $projectDto,
        #[DataCollectionOf(ObjectiveQuestionDto::class)]
        public DataCollection $objectiveQuestions,
        #[DataCollectionOf(ProjectInputDto::class)]
        public DataCollection $projectInputs,
        #[DataCollectionOf(ProjectOutputDto::class)]
        public DataCollection $projectOutputs,
        /** @var Collection<int, int> */
        public array $outputLanguages, //todo specify int type
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
