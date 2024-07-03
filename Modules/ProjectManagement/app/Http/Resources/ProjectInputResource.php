<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagement\app\Models\ProjectInput;
use Override;

/**
 * @property-read ProjectInput $resource
 */
final class ProjectInputResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    #[Override]
    public function toArray(Request $request)
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'data_type' => $this->resource->data_type,
            'is_required' => $this->resource->is_required,
            'max_length' => $this->resource->max_length,
            'description' => $this->resource->description,
            'values' => ProjectInputEnumValueResource::collection($this->whenLoaded('enumValues')),
        ];
    }
}
