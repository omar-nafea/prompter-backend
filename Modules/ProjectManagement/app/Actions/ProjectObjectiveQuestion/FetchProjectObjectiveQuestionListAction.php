<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\ProjectObjectiveQuestion;

use Illuminate\Support\Collection;
use Modules\ProjectManagement\app\Models\ProjectObjectiveQuestion;

final class FetchProjectObjectiveQuestionListAction
{
    /**
     * @return Collection<int,ProjectObjectiveQuestion>
     */
    public function execute(): Collection
    {
        return ProjectObjectiveQuestion::where('status', 1)->get();
    }
}
