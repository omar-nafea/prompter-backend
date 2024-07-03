<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;
use Modules\ProjectManagement\app\Http\Resources\ProjectOutputFormatResource;

final class ProjectOutputFormatController
{
    public function __invoke(): JsonResponse
    {
        return apiResponse()
            ->success()
            ->data(
                ProjectOutputFormatResource::collection(ProjectOutputFormat::cases())
            )->send();
    }
}
