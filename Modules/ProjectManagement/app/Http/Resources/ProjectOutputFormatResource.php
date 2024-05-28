<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;

class ProjectOutputFormatResource extends JsonResource
{
    public function toArray(Request $request)
    {
        /** @var ProjectOutputFormat|self $this */
        return [
            'id' => $this->value,
            'name' => $this->label(),
            'enabled' => $this->enabled(),
        ];
    }
}
