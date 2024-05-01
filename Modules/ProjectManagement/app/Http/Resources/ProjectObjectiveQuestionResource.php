<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagement\app\Models\ProjectObjectiveQuestion;

class ProjectObjectiveQuestionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ProjectObjectiveQuestion|self $this */
        return [
            'id' => $this->id,
            'question' => $this->question,
        ];
    }
}
