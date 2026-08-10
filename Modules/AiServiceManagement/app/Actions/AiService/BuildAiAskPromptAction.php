<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Actions\AiService;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use JsonException;
use Modules\ProjectManagement\app\Enums\ProjectQuestionType;
use Modules\ProjectManagement\app\Models\Project;
use Modules\ProjectManagement\app\Models\ProjectInput;
use Modules\ProjectManagement\app\Models\ProjectOutput;

final class BuildAiAskPromptAction
{
    /**
     * @param  array<string,mixed>  $inputsData
     *
     * @throws JsonException
     */
    public function execute(Project $project, array $inputsData): string
    {
        $string = Str::of($this->getPromptTemplate())
            ->replace(
                search: '[BACKGROUND]',
                replace: Str::minify(
                    $this->getQuestionAnswer(
                        project: $project,
                        questionType: ProjectQuestionType::Background
                    )
                )
            )
            ->replace(
                search: '[EXPECTED OUTCOMES]',
                replace: $project->expected_outcome
            )
            ->replace(
                search: '[LIST OF OUTPUTS WITH DESCRIPTION OF EACH IF AVAILABLE]',
                replace: $this->prepareOutputsWithDescription(
                    projectOutputs: $project->outputs,
                    separator: ','
                )
            )
            ->replace(
                search: '[LIST OF INPUTS WITH DESCRIPTION OF EACH IF AVAILABLE]',
                replace: $this->prepareInputsWithDescription(
                    projectInputs: $project->inputs,
                    separator: ','
                )
            )
            ->replace(
                search: '[FORMAT]',
                replace: $project->output_format->label()
            )
            ->replace(
                search: '[INPUTS WITH VALUES]',
                replace: $this->prepareInputsWithValues(inputsData: $inputsData)
            )
            ->replace(
                search: '[LANGUAGE]',
                replace: $project->outputLanguages->pluck(value: 'name')->join(glue: ',')
            )
            ->replace(
                search: '[OUTPUTS WITH DESCRIPTION]',
                replace: $this->prepareOutputsWithDescription(
                    projectOutputs: $project->outputs,
                    withWrapper: true,
                    separator: '.'
                )
            )
            ->replace(
                search: '[OUTPUT MAXIMUM LENGTH]',
                replace: (string) $project->max_output_length
            )
            ->toString();

        return $string;
    }

    /**
     * @param  Collection<int, ProjectOutput>  $projectOutputs
     */
    protected function prepareOutputsWithDescription(Collection $projectOutputs, bool $withWrapper = false, string $separator = ';'): string
    {
        return (string) Str::minify(
            value: $projectOutputs->reduce(
                callback: function (string $accumulator, ProjectOutput $projectOutput, int $idx) use ($projectOutputs, $withWrapper, $separator) {
                    if ($idx === $projectOutputs->count() - 1) {
                        $separator = '';
                    }
                    $pattern = '[%s]:%s ' . $separator . ' ';
                    $accumulator .= $withWrapper ?
                        sprintf($pattern, $projectOutput->name, Str::minify($projectOutput->description, ' ')) :
                        sprintf(str_replace(search: ['[', ']'], replace: ['', ''], subject: $pattern), $projectOutput->name, $projectOutput->description);

                    //            $accumulator .= sprintf('[%s], %s;', $projectOutput->name, $projectOutput->description);
                    return $accumulator;
                },
                initial: ''
            ),
            separator: ' ',
        );
    }

    /**
     * @param  Collection<int, ProjectInput>  $projectInputs
     */
    protected function prepareInputsWithDescription(Collection $projectInputs, bool $withWrapper = false, string $separator = ';'): string
    {
        return (string) Str::minify(
            value: $projectInputs->reduce(
                callback: function (string $accumulator, ProjectInput $projectInput, int $idx) use ($projectInputs, $separator, $withWrapper) {
                    if ($idx === $projectInputs->count() - 1) {
                        $separator = '';
                    }
                    $pattern = '[%s]: %s' . $separator;

                    if ($projectInput->description === null) {
                        $pattern = '[%s]' . $separator;
                    }
                    $accumulator .= $withWrapper ?
                        sprintf($pattern, $projectInput->name, $projectInput->description) :
                        sprintf(str_replace(search: ['[', ']'], replace: ['', ''], subject: $pattern), $projectInput->name, $projectInput->description);

                    return $accumulator;
                },
                initial: ''
            )
        );
    }

    /**
     * @param  array<string, mixed>  $inputsData
     *
     * @throws JsonException
     */
    protected function prepareInputsWithValues(array $inputsData): string
    {
        foreach ($inputsData as $key => $value) {
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (is_array($decoded)) {
                    $inputsData[$key] = $decoded;
                }
            }
        }

        return json_encode(
            $inputsData,
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        );
    }

    protected function getPromptTemplate(): string
    {
        return <<<'PROMPT'
Task:
[EXPECTED OUTCOMES]

Context and policy:
[BACKGROUND]

Input contract:
[LIST OF INPUTS WITH DESCRIPTION OF EACH IF AVAILABLE]

Input data:
[INPUTS WITH VALUES]

Output contract:
[OUTPUTS WITH DESCRIPTION]

Return only valid [FORMAT] in [LANGUAGE], with no markdown fences or commentary. Keep the response within [OUTPUT MAXIMUM LENGTH] characters.
PROMPT;
    }

    protected function getQuestionAnswer(Project $project, ProjectQuestionType $questionType): ?string
    {
        return $project->loadMissing('answers')
            ->answers
            ->firstWhere(key: 'project_objective_question_id', operator: $questionType->value)?->answer ?? '';
    }
}
