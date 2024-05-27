<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagement\App\Models\ProjectOutputLanguage;

class ProjectOutputLanguageResource extends JsonResource
{
    public function toArray(Request $request)
    {
        /** @var ProjectOutputLanguage|self $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
