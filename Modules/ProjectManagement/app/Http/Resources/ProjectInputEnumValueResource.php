<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagement\app\Models\ProjectInputEnumValue;
use Override;

/**
 * @property-read ProjectInputEnumValue $resource
 */
final class ProjectInputEnumValueResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    #[Override]
    public function toArray(Request $request)
    {
        return [
            'id' => $this->resource->id,
            'value' => $this->resource->value,
        ];
    }
}
