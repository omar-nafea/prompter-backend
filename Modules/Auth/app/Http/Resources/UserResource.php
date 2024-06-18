<?php

declare(strict_types=1);

namespace Modules\Auth\app\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Auth\app\Models\User;
use Override;

/**
 * @property-read User $resource
 */
final class UserResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    #[Override]
    public function toArray($request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email->toNative(),
            'phone' => $this->resource->phone?->toNative(),
            'status' => [
                'name' => $this->resource->status->label(),
                'value' => $this->resource->status->value,
            ],
        ];

    }
}
