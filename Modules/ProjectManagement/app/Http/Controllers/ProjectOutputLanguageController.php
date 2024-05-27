<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\App\Http\Controllers;

use Modules\ProjectManagement\App\Http\Resources\ProjectOutputLanguageResource;
use Modules\ProjectManagement\App\Models\ProjectOutputLanguage;

class ProjectOutputLanguageController
{
    public function __invoke()
    {
        return apiResponse()
            ->success()
            ->data(
                ProjectOutputLanguageResource::collection(ProjectOutputLanguage::enabled()->get())
            );
    }
}
