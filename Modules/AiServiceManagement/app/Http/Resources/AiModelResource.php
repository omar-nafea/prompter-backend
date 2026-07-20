<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\AiServiceManagement\app\Models\AiModel;
use Override;

/**
 * @property-read AiModel $resource
 */
final class AiModelResource extends JsonResource
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
            'alias' => $this->resource->alias,
            'provider' => [
                'name' => $this->resource->provider->label(),
                'value' => $this->resource->provider->value,
            ],
            'connector_url' => $this->resource->connector_url,
            'api_key_hint' => $this->resource->api_key_hint,
            'has_api_key' => filled($this->resource->api_key),
        ];
    }
}
