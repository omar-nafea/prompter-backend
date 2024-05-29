<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\AiServiceManagement\app\Models\AiService;

class AiServiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var AiService|self $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => [
                'name' => $this->status->label(),
                'value' => $this->status->value,
            ],
        ];
    }
}
