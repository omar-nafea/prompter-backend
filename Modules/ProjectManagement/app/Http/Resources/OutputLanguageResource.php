<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagement\app\Models\OutputLanguage;
use Override;

/**
 * @property-read OutputLanguage $resource
 */
final class OutputLanguageResource extends JsonResource
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
            'status' => [
                'name' => $this->resource->status->label(),
                'value' => $this->resource->status->value,
            ],
        ];
    }
}
