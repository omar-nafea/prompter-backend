<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Dtos\Project;

use App\Dtos\BaseDto;
use Spatie\LaravelData\Attributes\MapOutputName;

final class ObjectiveQuestionDto extends BaseDto
{
    public function __construct(
        #[MapOutputName('project_objective_question_id')]
        public int $questionId,
        public string $answer
    ) {}
}
