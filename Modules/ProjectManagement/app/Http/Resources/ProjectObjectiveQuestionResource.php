<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagement\app\Models\ProjectObjectiveQuestion;
use Override;

/**
 * @property-read ProjectObjectiveQuestion $resource
 */
final class ProjectObjectiveQuestionResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'question' => $this->resource->question,
        ];
    }
}
