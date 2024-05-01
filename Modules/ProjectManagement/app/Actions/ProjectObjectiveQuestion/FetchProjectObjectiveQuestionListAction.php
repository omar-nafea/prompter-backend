<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\ProjectObjectiveQuestion;

use Modules\ProjectManagement\app\Models\ProjectObjectiveQuestion;

class FetchProjectObjectiveQuestionListAction
{
    public function execute()
    {
        return ProjectObjectiveQuestion::where('status', 1)->get();
    }
}
