<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation;

use Exception;
use Modules\AiServiceManagement\app\Enums\AiCallType;
use Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation\Strategies\Contracts\AiCallTypeStrategy;
use Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation\Strategies\OneByOneCall;
use Modules\ProjectManagement\app\Models\Project;

class AiServiceInputsValidationFactory
{
    public static function make(Project $project): AiCallTypeStrategy
    {
        $mapping = [
            AiCallType::OneByOne->value => OneByOneCall::class,
            AiCallType::Bulk->value => OneByOneCall::class, // todo implement bulk
        ];
        if (! isset($mapping[$project->aiCallType->type->value])) {
            throw new Exception('Ai call type not supported' . $project->aiCallType->type->value);
        }
        $class = $mapping[$project->aiCallType->type->value];

        return app($class, ['project' => $project]);
    }
}
