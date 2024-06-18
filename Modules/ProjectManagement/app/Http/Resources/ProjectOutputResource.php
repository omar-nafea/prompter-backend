<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagement\app\Models\ProjectOutput;
use Override;

/**
 * @property-read ProjectOutput $resource
 */
final class ProjectOutputResource extends JsonResource
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
        ];
    }
}
