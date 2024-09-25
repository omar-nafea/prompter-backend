<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\Project;

use Cache;
use Exception;
use Modules\AiServiceManagement\app\Actions\AiService\BuildAiAskPromptAction;
use Modules\ProjectManagement\app\Enums\DataType;
use Modules\ProjectManagement\app\Models\Project;
use Modules\ProjectManagement\app\Models\ProjectInput;
use Str;

final readonly class CheckProjectPromptHasExceededMaxTokensAction
{
    public function __construct(
        private BuildAiAskPromptAction $buildAiAskPromptAction,
    ) {}

    public function execute(Project $project): bool
    {
        try {
            return Cache::tags(
                Project::cacheTag(),
            )->rememberForever(
                md5(sprintf('project-prompt-exceeded-tokens-%s', $project->id)),
                function () use ($project) {
                    $project->load([
                        'answers',
                        'outputLanguages',
                        'inputs.enumValues',
                        'outputs.enumValues',
                    ]);
                    $promptPayload = [];
                    foreach ($project->inputs as $input) {
                        /** @var ProjectInput $input */
                        $promptPayload[$input->name] = match ($input->data_type) {
                            DataType::String => Str::random($input->max_length ?? 0),
                            DataType::Boolean => Str::random(mb_strlen('FALSE')),
                            DataType::Integer, DataType::Float => $input->max_length,
                            DataType::Enum, => $input->enumValues->map(
                                static fn ($enumValue) => $enumValue->value
                            )->sortByDesc(
                                static fn (string $a, string $b) => mb_strlen($a) <=> mb_strlen($b)
                            )->first(),
                            default => throw new Exception('Unexpected match value' . $input->data_type->value),
                        };
                    }
                    $prompt = $this->buildAiAskPromptAction->execute(
                        project: $project,
                        inputsData: $promptPayload
                    );

                    return mb_strlen($prompt) > $project->aiService->max_characters;
                }
            );
        } catch (Exception $exception) {
            report($exception);

            return false;
        }
    }
}
