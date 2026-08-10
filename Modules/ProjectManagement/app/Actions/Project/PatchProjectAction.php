<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\Project;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Modules\ProjectManagement\app\Enums\DataType;
use Modules\ProjectManagement\app\Models\Project;

final class PatchProjectAction
{
    /** @param array<string, mixed> $patch */
    public function execute(Project $project, array $patch): Project
    {
        return DB::transaction(function () use ($project, $patch): Project {
            $this->patchProject($project, $patch);
            /** @var array<int, array{answer_id: int, answer: string}> $answerPatches */
            $answerPatches = $patch['objective_questions'] ?? [];
            /** @var array<int, array<string, mixed>> $inputPatches */
            $inputPatches = $patch['project_inputs'] ?? [];
            /** @var array<int, array<string, mixed>> $outputPatches */
            $outputPatches = $patch['project_outputs'] ?? [];
            $this->patchObjectiveAnswers($project, $answerPatches);
            $this->patchInputs($project, $inputPatches);
            $this->patchOutputs($project, $outputPatches);

            return $this->loadProject($project);
        });
    }

    /** @param array<string, mixed> $patch */
    private function patchProject(Project $project, array $patch): void
    {
        $project->update(Arr::only($patch, [
            'name',
            'expected_outcome',
            'max_output_length',
            'output_format',
        ]));

        if (array_key_exists('output_languages', $patch)) {
            /** @var int[] $outputLanguages */
            $outputLanguages = $patch['output_languages'];
            $project->outputLanguages()->sync($outputLanguages);
        }
    }

    /** @param array<int, array{answer_id: int, answer: string}> $answers */
    private function patchObjectiveAnswers(Project $project, array $answers): void
    {
        foreach ($answers as $answerPatch) {
            $project->answers()
                ->findOrFail($answerPatch['answer_id'])
                ->update(['answer' => $answerPatch['answer']]);
        }
    }

    /** @param array<int, array<string, mixed>> $inputs */
    private function patchInputs(Project $project, array $inputs): void
    {
        foreach ($inputs as $inputPatch) {
            $input = $project->inputs()->findOrFail($inputPatch['id']);
            /** @var string[] $enumValues */
            $enumValues = $inputPatch['values'] ?? [];
            $input->update(Arr::except($inputPatch, ['id', 'values']));

            if (array_key_exists('values', $inputPatch) || $this->setsNonEnumType($inputPatch, $input->data_type)) {
                $input->enumValues()->delete();
                $input->enumValues()->createMany(
                    array_map(static fn (string $enumValue): array => ['value' => $enumValue], $enumValues)
                );
            }
        }
    }

    /** @param array<int, array<string, mixed>> $outputs */
    private function patchOutputs(Project $project, array $outputs): void
    {
        foreach ($outputs as $outputPatch) {
            $output = $project->outputs()->findOrFail($outputPatch['id']);
            /** @var string[] $enumValues */
            $enumValues = $outputPatch['values'] ?? [];
            $output->update(Arr::except($outputPatch, ['id', 'values']));

            if (array_key_exists('values', $outputPatch) || $this->setsNonEnumType($outputPatch, $output->data_type)) {
                $output->enumValues()->delete();
                $output->enumValues()->createMany(
                    array_map(static fn (string $enumValue): array => ['value' => $enumValue], $enumValues)
                );
            }
        }
    }

    /** @param array<string, mixed> $fieldPatch */
    private function setsNonEnumType(array $fieldPatch, DataType $currentDataType): bool
    {
        return array_key_exists('data_type', $fieldPatch) && $currentDataType !== DataType::Enum;
    }

    private function loadProject(Project $project): Project
    {
        return $project->refresh()->load([
            'inputs.enumValues',
            'outputs.enumValues',
            'answers.objectiveQuestion',
            'aiCallType',
            'aiResponseType',
            'outputLanguages',
            'details',
            'aiModel',
        ]);
    }
}
