<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Resources;

use App\Http\Resources\DateTimeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\AiServiceManagement\app\Http\Resources\AiCallTypeResource;
use Modules\AiServiceManagement\app\Http\Resources\AiResponseTypeResource;
use Modules\AiServiceManagement\app\Http\Resources\AiServiceResource;
use Modules\ProjectManagement\app\Models\Project;
use Override;

/**
 * @property-read Project $resource
 */
final class ProjectResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    #[Override]
    public function toArray(Request $request)
    {
        return [
            'id' => $this->resource->key,
            'name' => $this->resource->name,
            'expected_outcome' => $this->resource->expected_outcome,
            'max_output_length' => $this->resource->max_output_length,
            'output_format' => [
                'name' => $this->resource->output_format->label(),
                'value' => $this->resource->output_format->value,
            ],
            'api_key' => $this->resource->api_key,
            'public_key' => $this->resource->key,
            'status' => [
                'name' => $this->resource->status,
                'value' => $this->resource->status,
            ],
            'created_at' => DateTimeResource::make($this->resource->created_at),
            'updated_at' => DateTimeResource::make($this->resource->updated_at),
            'output_languages' => OutputLanguageResource::collection($this->whenLoaded('outputLanguages')),
            'ai_service' => AiServiceResource::make($this->whenLoaded('aiService')),
            'ai_call_type' => AiCallTypeResource::make($this->whenLoaded('aiCallType')),
            'ai_response_type' => AiResponseTypeResource::make($this->whenLoaded('aiResponseType')),
            'inputs' => ProjectInputResource::collection($this->whenLoaded('inputs')),
            'outputs' => ProjectInputResource::collection($this->whenLoaded('outputs')),
            'answers' => ProjectObjectiveAnswerResource::collection($this->whenLoaded('answers')),
        ];
    }
}
