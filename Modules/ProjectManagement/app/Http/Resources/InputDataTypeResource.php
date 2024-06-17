<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagement\app\Enums\DataType;
use Override;

/**
 * @property-read DataType $resource
 */

final class InputDataTypeResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->resource->label(),
            'value' => $this->resource->value,
        ];
    }
}
