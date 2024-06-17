<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagement\app\Models\ProjectObjectiveAnswer;
use Override;

/**
 * @property-read ProjectObjectiveAnswer $resource
 */
final class ProjectObjectiveAnswerResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    #[Override]
    public function toArray(Request $request)
    {
        return [
            'id' => $this->resource->id,
            'objective_question' => ProjectObjectiveQuestionResource::make($this->whenLoaded('objectiveQuestion')),
            'project_id' => ProjectResource::make($this->whenLoaded('project')),
            'answer' => $this->resource->answer,
        ];
    }
}
