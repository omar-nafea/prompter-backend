<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\AiServiceManagement\app\Enums\AiModelProvider;

final class AiModelController
{
    public function providers(): JsonResponse
    {
        return apiResponse()->success()->data([
            'providers' => AiModelProvider::selectOptions(),
        ])->send();
    }
}
