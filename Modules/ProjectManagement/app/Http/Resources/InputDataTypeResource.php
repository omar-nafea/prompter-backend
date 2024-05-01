<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagement\app\Enums\DataType;

class InputDataTypeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var DataType|self $this */
        return [
            'name' => $this->label(),
            'value' => $this->name,
        ];
    }
}
