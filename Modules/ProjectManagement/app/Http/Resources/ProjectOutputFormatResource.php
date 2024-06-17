<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;
use Override;

/**
 * @property-read ProjectOutputFormat $resource
 */

final class ProjectOutputFormatResource extends JsonResource
{
    /**
     * @return array<string,mixed>
     */
    #[Override]
    public function toArray(Request $request)
    {
        return [
            'id' => $this->resource->value,
            'name' => $this->resource->label(),
            'enabled' => $this->resource->enabled(),
        ];
    }
}
