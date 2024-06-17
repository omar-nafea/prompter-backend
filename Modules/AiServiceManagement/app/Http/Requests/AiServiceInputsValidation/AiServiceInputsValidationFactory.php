<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Modules\AiServiceManagement\app\Enums\AiCallType;
use Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation\Strategies\Contracts\AiCallTypeStrategy;
use Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation\Strategies\OneByOneCall;
use Modules\ProjectManagement\app\Models\Project;

final class AiServiceInputsValidationFactory
{
    public function __construct(
        protected Container $app,
        protected Project $project,
    ) {}

    /**
     * @throws Exception
     */
    public static function make(Project $project): self
    {
        /** @var Container $app */
        $app = app();
        /** @var AiServiceInputsValidationFactory */
        return  $app->make(self::class, compact('project'));
    }

    /**
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function handle(): AiCallTypeStrategy
    {
        $class = match ($this->project->aiCallType->type) {
            AiCallType::OneByOne => OneByOneCall::class,
            AiCallType::Bulk => OneByOneCall::class, // todo implement bulk
            default => throw new Exception(
                'Ai call type not supported' . $this->project->aiCallType->type->value
            )
        };

        /** @var AiCallTypeStrategy*/
        return $this->app->make($class, ['project' => $this->project]);
    }
}
