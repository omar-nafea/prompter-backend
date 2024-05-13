<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagement\app\Models\ProjectObjectiveAnswer;

class ProjectObjectiveAnswerResource extends JsonResource
{
    public function toArray(Request $request)
    {
        /** @var ProjectObjectiveAnswer|self $this */
        return [
            'id' => $this->id,
            'objective_question' => ProjectObjectiveQuestionResource::make($this->whenLoaded('objectiveQuestion')),
            'project_id' => ProjectResource::make($this->whenLoaded('project')),
            'answer' => $this->answer,
        ];
    }
}
