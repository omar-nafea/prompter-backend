<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\AiServiceManagement\app\Models\AiService;
use Override;

/**
 * @property-read AiService $resource
 */
final class AiServiceResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'status' => [
                'name' => $this->resource->status->label(),
                'value' => $this->resource->status->value,
            ],
            'default_temperature' => $this->resource->default_temperature,
            'supports_configurable_temperature' => $this->resource->supports_configurable_temperature,
        ];
    }
}
