<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagement\app\Models\OutputLanguage;

class OutputLanguageResource extends JsonResource
{
    public function toArray(Request $request)
    {
        /** @var OutputLanguage|self $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => [
                'name' => $this->status?->label(),
                'value' => $this->status?->value,
            ],
        ];
    }
}
