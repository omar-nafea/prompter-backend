<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\AiServiceManagement\app\Models\AiCallType;
use Override;

/**
 * @property-read AiCallType $resource
 */
final class AiResponseTypeResource extends JsonResource
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
            'type' => $this->resource->type,
            'status' => [
                'name' => $this->resource->status->label(),
                'value' => $this->resource->status->value,
            ],
        ];
    }
}
