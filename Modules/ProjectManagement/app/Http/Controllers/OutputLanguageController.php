<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\ProjectManagement\app\Http\Resources\OutputLanguageResource;
use Modules\ProjectManagement\app\Models\OutputLanguage;

final class OutputLanguageController
{
    public function __invoke(): JsonResponse
    {
        //todo move logic to action
        return apiResponse()
            ->success()
            ->data(
                OutputLanguageResource::collection(
                    OutputLanguage::enabled()->get()
                )
            )->send();
    }
}
