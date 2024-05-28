<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Controllers;

use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;
use Modules\ProjectManagement\app\Http\Resources\ProjectOutputFormatResource;

class ProjectOutputFormatController
{
    public function __invoke()
    {
        return apiResponse()
            ->success()
            ->data(
                ProjectOutputFormatResource::collection(ProjectOutputFormat::cases())
            )->send();
    }
}
