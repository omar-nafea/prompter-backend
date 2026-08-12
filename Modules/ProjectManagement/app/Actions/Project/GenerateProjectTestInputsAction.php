<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\Project;

use Illuminate\Support\Arr;
use JsonException;
use Modules\AiServiceManagement\app\Gateway\AiProviderResolver;
use Modules\AiServiceManagement\app\Gateway\Dtos\AiCompletionRequest;
use Modules\AiServiceManagement\app\Models\AiModel;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Enums\DataType;
use Modules\ProjectManagement\app\Models\Project;
use Modules\ProjectManagement\app\Models\ProjectInput;
use RuntimeException;

final class GenerateProjectTestInputsAction
{
    public function __construct(
        protected AiProviderResolver $resolver,
    ) {}

    /**
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    public function execute(string $projectKey, User $user): array
    {
        $project = Project::query()
            ->allowedForUser($user)
            ->with('inputs.enumValues')
            ->where('key', $projectKey)
            ->firstOrFail();
        $model = AiModel::query()->whereNull('project_id')->firstOrFail();
        $response = $this->resolver->for($model->provider)->complete($model, new AiCompletionRequest(
            prompt: $this->prompt($project),
            systemPrompt: 'Generate realistic test data for every project input. Return one compact JSON object only, using each input name exactly once. For JSON inputs, return a small representative object rather than a full source document and keep it under 8 KB. Do not use markdown or commentary.',
            temperature: 0.2,
            maxOutputTokens: 4096,
            // ponytail: arbitrary JSON cannot use strict schemas; add a declared JSON schema when supported.
            responseSchema: $project->inputs->contains(
                fn (ProjectInput $input): bool => $input->data_type === DataType::Json
            ) ? null : $this->schema($project),
            responseSchemaName: 'project_test_inputs',
        ));
        $generated = $response->data();
        $inputNames = $project->inputs->pluck('name')->all();

        if (($generated !== [] && array_is_list($generated)) || array_diff($inputNames, array_keys($generated))) {
            throw new RuntimeException('The default model did not return valid values for every project input.');
        }

        return Arr::only($generated, $inputNames);
    }

    /**
     * @throws JsonException
     */
    private function prompt(Project $project): string
    {
        $inputs = $project->inputs->map(static fn (ProjectInput $input): array => [
            'name' => $input->name,
            'type' => $input->data_type->label(),
            'required' => $input->is_required,
            'description' => $input->description,
            'options' => $input->data_type === DataType::Enum
                ? $input->enumValues->map(fn ($option): array => [
                    'id' => $option->id,
                    'value' => $option->value,
                ])->values()->all()
                : null,
        ])->values()->all();

        return 'Create one realistic test input object for this contract. Use each input name exactly as provided. '
            . 'For categorical inputs, return an array of option IDs. For JSON inputs, return a compact representative object, not a full source document. Contract: '
            . json_encode($inputs, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
    }

    /**
     * @return array<string, mixed>
     */
    private function schema(Project $project): array
    {
        $properties = [];
        foreach ($project->inputs as $input) {
            $properties[$input->name] = $this->propertySchema($input);
        }

        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => array_keys($properties),
            'properties' => $properties,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function propertySchema(ProjectInput $input): array
    {
        return match ($input->data_type) {
            DataType::String => array_filter([
                'type' => 'string',
                'maxLength' => $input->max_length ?: null,
            ]),
            DataType::Integer => ['type' => 'integer'],
            DataType::Float => ['type' => 'number'],
            DataType::Boolean => ['type' => 'boolean'],
            DataType::Enum => [
                'type' => 'array',
                'minItems' => 1,
                'items' => array_filter([
                    'type' => 'integer',
                    'enum' => $input->enumValues->pluck('id')->values()->all() ?: null,
                ]),
            ],
            DataType::Json => ['type' => 'object'],
        };
    }
}
