<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\App\Http\Controllers;

use Modules\ProjectManagement\App\Enums\ProjectOutputFormat;
use Modules\ProjectManagement\App\Http\Resources\ProjectOutputFormatResource;

class ProjectOutputFormatController
{
    public function __invoke()
    {
        return apiResponse()->success()->data(
            ProjectOutputFormatResource::collection(ProjectOutputFormat::cases())
        );
    }
}
