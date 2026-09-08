<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\Project;

use Illuminate\Validation\ValidationException;
use Modules\AiServiceManagement\app\Enums\AiModelProvider;
use Modules\AiServiceManagement\app\Gateway\AiProviderResolver;
use Modules\AiServiceManagement\app\Models\AiModel;
use Modules\ProjectManagement\app\Dtos\Project\ProjectAiModelDto;
use Modules\ProjectManagement\app\Models\Project;

final class PersistProjectAiModelAction
{
    public function __construct(
        protected AiProviderResolver $resolver,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(Project $project, ?ProjectAiModelDto $dto): ?AiModel
    {
        $existing = $project->aiModel;
        if ($dto === null) {
            return $existing;
        }

        $apiKey = $dto->apiKey ?? $existing?->api_key;
        $connectorUrl = $dto->provider === AiModelProvider::OpenAiCompatible
            ? $dto->connectorUrl
            : null;

        if (filled($apiKey)) {
            $candidate = new AiModel([
                'name' => $dto->name,
                'alias' => $dto->alias,
                'provider' => $dto->provider,
                'api_key' => $apiKey,
                'connector_url' => $connectorUrl,
            ]);

            $connection = $this->resolver->for($candidate->provider)->test($candidate);
            if ( ! $connection['success']) {
                throw ValidationException::withMessages([
                    'ai_model_connection' => $connection['message'],
                ]);
            }
        }

        // Never persist a model configuration without a usable credential:
        // an empty key takes precedence over the application default and
        // fails on the first AI request.
        $effectiveKey = $dto->apiKey ?? $existing?->api_key;
        if ( ! filled($effectiveKey)) {
            throw ValidationException::withMessages([
                'ai_model_api_key' => 'An API key is required to configure the AI model.',
            ]);
        }

        $attributes = [
            'name' => $dto->name,
            'alias' => $dto->alias,
            'provider' => $dto->provider,
            'connector_url' => $connectorUrl,
        ];
        if ($existing === null || filled($dto->apiKey)) {
            $attributes['api_key'] = (string) $effectiveKey;
        }

        /** @var AiModel $model */
        $model = $project->aiModel()->updateOrCreate([], $attributes);

        return $model;
    }
}
