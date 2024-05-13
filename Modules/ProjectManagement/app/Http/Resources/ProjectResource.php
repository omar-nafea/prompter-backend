<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\AiServiceManagement\app\Http\Resources\AiCallTypeResource;
use Modules\AiServiceManagement\app\Http\Resources\AiResponseTypeResource;
use Modules\AiServiceManagement\app\Http\Resources\AiServiceResource;
use Modules\ProjectManagement\app\Models\Project;

class ProjectResource extends JsonResource
{
    public function toArray(Request $request)
    {
        /** @var Project|self $this */
        return [
            'id' => $this->key,
            'name' => $this->name,
            'expected_outcome' => $this->expected_outcome,
            'api_key' => $this->api_key,
            'status' => [
                'name' => $this->status,
                'value' => $this->status,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'ai_service' => AiServiceResource::make($this->whenLoaded('aiService')),
            'ai_call_type' => AiCallTypeResource::make($this->whenLoaded('aiCallType')),
            'ai_response_type' => AiResponseTypeResource::make($this->whenLoaded('aiResponseType')),
            'inputs' => ProjectInputResource::collection($this->whenLoaded('inputs')),
            'outputs' => ProjectInputResource::collection($this->whenLoaded('outputs')),
            'answers' => ProjectObjectiveAnswerResource::collection($this->whenLoaded('answers')),
        ];
    }
}
