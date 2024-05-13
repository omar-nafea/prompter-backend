<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagement\app\Models\ProjectOutput;

class ProjectOutputResource extends JsonResource
{
    public function toArray(Request $request)
    {
        /** @var ProjectOutput|self $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'data_type' => $this->data_type,
            'is_required' => $this->is_required,
            'max_length' => $this->max_length,
            'description' => $this->description,
        ];
    }
}
