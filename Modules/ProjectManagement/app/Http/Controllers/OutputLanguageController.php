<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Controllers;

use Modules\ProjectManagement\app\Http\Resources\OutputLanguageResource;
use Modules\ProjectManagement\app\Models\OutputLanguage;

class OutputLanguageController
{
    public function __invoke()
    {
        return apiResponse()
            ->success()
            ->data(
                OutputLanguageResource::collection(OutputLanguage::enabled()->get())
            )->send();
    }
}
