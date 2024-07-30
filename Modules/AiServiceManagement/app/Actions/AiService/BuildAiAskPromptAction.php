<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Actions\AiService;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Modules\ProjectManagement\app\Enums\ProjectQuestionType;
use Modules\ProjectManagement\app\Models\Project;
use Modules\ProjectManagement\app\Models\ProjectInput;
use Modules\ProjectManagement\app\Models\ProjectOutput;

final class BuildAiAskPromptAction
{
    /**
     * @param  array<string,mixed>  $inputsData
     *
     * @throws Exception
     */
    public function execute(Project $project, array $inputsData): string
    {
        //        dd($project->expected_outcome);

        //        $project->background = '### Tawfeer Market Context:
        //Tawfeer Market is an online grocery startup in Egypt that offers a convenient and affordable way to shop for groceries online and get them delivered to your door in less than 2 hours. Tawfeer has its own fulfillment centers and stores, which gives it more control over its supply chain and value delivery. Tawfeer also has a powerful in-house marketing department that reaches its target market with cost-efficiency and low customer acquisition cost.
        //
        //### Available coupons
        //[First50] discount of 50EGP for a minimum order of 750 EGP valid for the first order only
        //[FreeD] Discount of 20EGP for a minimum order of 500 EGP valid for all customers
        //Never propose a coupon that is not listed here
        //
        //### Products currently at discount
        //White tissue kitchen towel 3+1 Roll 77.95 RGP instead of 91.50 EGP
        //Capucci 1 pc 7.00 EGP instead of 10.00 EGP';
        //        $project->expected_outcome = 'Given the following customer data and context for Tawfeer Market, generate a personalized marketing message strategy';
        //        dd($project->expected_outcome);
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

        //        dd($string);

        return $string;
        //        response()->json(compact('string'))->throwResponse();
        //        dd($string);
        //        $str = sprintf(
        //            $string,
        //            $project->background,
        //            $project->expected_outcome,
        //            'json', // todo add format to project table
        //            $this->prepareOutputsWithDescription($project->outputs),
        //            $this->prepareInputsWithDescription($project->inputs),
        //            'json', // todo add format to project table
        //            $this->prepareInputsWithValues($inputsData),
        //            'english', // todo add language to project table
        //            $this->prepareOutputsWithDescription($project->outputs),
        //            1000 // todo add output maximum to project table
        //        );
        //replace placeholders
        //remove line breaks and double ""

        //validate the response is valid format and have valid outputs

        //        return $prompt;
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
     * @throws Exception
     */
    protected function prepareInputsWithValues(array $inputsData, bool $withWrapper = false, string $separator = ' '): string
    {
        return (string) Str::minify(
            value: collect($inputsData)->reduce(
                callback: static function (string $accumulator, mixed $value, mixed $key) use ($separator, $withWrapper) {
                    if ( ! (is_bool($value) || is_string($value) || is_int($value))) {
                        throw new Exception('value must be string, int or bool :' . gettype($value) . $value);
                    }
                    $pattern = '-%s:%s' . $separator;
                    $accumulator .= $withWrapper ?
                        sprintf($pattern, $key, is_bool($value) ? ($value ? 'yes' : 'no') : $value) :
                        sprintf(str_replace(search: ['[', ']'], replace: ['', ''], subject: $pattern), $key, $value);

                    return $accumulator;
                },
                initial: '',
            )
        );
    }

    protected function getPromptTemplate(): string
    {
        return '[EXPECTED OUTCOMES] in [FORMAT] format. The output should specify the [LIST OF OUTPUTS WITH DESCRIPTION OF EACH IF AVAILABLE]accordingly. The output should be [FORMAT] only and nothing else in the message . Consider the [LIST OF INPUTS WITH DESCRIPTION OF EACH IF AVAILABLE] to tailor the response accordingly.[BACKGROUND]. The output should be [FORMAT] only and nothing else in the message. [INPUTS WITH VALUES]. Application display Language: [LANGUAGE].Generate a [FORMAT] output that includes the following: [OUTPUTS WITH DESCRIPTION]. The output should be (Maximum [OUTPUT MAXIMUM LENGTH])';
    }

    protected function getQuestionAnswer(Project $project, ProjectQuestionType $questionType): ?string
    {
        return $project->loadMissing('answers')
            ->answers
            ->firstWhere(key: 'project_objective_question_id', operator: $questionType->value)?->answer ?? '';
    }
}
