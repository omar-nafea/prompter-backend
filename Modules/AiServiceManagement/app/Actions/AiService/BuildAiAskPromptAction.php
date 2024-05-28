<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Actions\AiService;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Modules\ProjectManagement\app\Enums\ProjectQuestionType;
use Modules\ProjectManagement\app\Models\Project;
use Modules\ProjectManagement\app\Models\ProjectInput;
use Modules\ProjectManagement\app\Models\ProjectOutput;

final class BuildAiAskPromptAction
{
    public function execute(Project $project, array $inputsData)
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
        $string = str($this->getPromptTemplate())
            ->replace('[BACKGROUND]', Str::minify($this->getQuestionAnswer($project, ProjectQuestionType::Background)))
            ->replace('[EXPECTED OUTCOMES]', $project->expected_outcome)
            ->replace(
                '[LIST OF OUTPUTS WITH DESCRIPTION OF EACH IF AVAILABLE]',
                $this->prepareOutputsWithDescription(projectOutputs: $project->outputs, separator: ',')
            )
            ->replace(
                '[LIST OF INPUTS WITH DESCRIPTION OF EACH IF AVAILABLE]',
                $this->prepareInputsWithDescription($project->inputs, false, ',')
            )
            ->replace('[FORMAT]', $project->output_format->label())// todo add format to project table
            ->replace(
                '[INPUTS WITH VALUES]',
                $this->prepareInputsWithValues($inputsData)
            )
            ->replace('[LANGUAGE]', $project->outputLanguages->pluck('name')->join(','))// todo add language to project table
            ->replace(
                '[OUTPUTS WITH DESCRIPTION]',
                $this->prepareOutputsWithDescription($project->outputs, true, '.')
            )
            ->replace('[OUTPUT MAXIMUM LENGTH]', $project->max_output_length) // todo add output maximum to project table
            ->toString();

        dd($string);

        return $string;
        response()->json(compact('string'))->throwResponse();
        dd($string);
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

        return $prompt;
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
                        sprintf(str_replace(['[', ']'], ['', ''], $pattern), $projectOutput->name, $projectOutput->description);

                    //            $accumulator .= sprintf('[%s], %s;', $projectOutput->name, $projectOutput->description);
                    return $accumulator;
                },
                initial: ''
            ),
            separator: ' ',
        );
    }

    protected function prepareInputsWithDescription(Collection $projectInputs, bool $withWrapper = false, string $separator = ';'): string
    {
        return (string) Str::minify(
            value: $projectInputs->reduce(
                callback: function (string $accumulator, ProjectInput $projectInput, int $idx) use ($projectInputs, $withWrapper, $separator) {
                    if ($idx === $projectInputs->count() - 1) {
                        $separator = '';
                    }
                    $pattern = '[%s]: %s' . $separator;

                    if ($projectInput->description === null) {
                        $pattern = '[%s]' . $separator;
                    }
                    $accumulator .= $withWrapper ?
                        sprintf($pattern, $projectInput->name, $projectInput->description) :
                        sprintf(str_replace(['[', ']'], ['', ''], $pattern), $projectInput->name, $projectInput->description);

                    return $accumulator;
                },
                initial: ''
            )
        );
    }

    protected function prepareInputsWithValues(array $inputsData, bool $withWrapper = false, string $separator = ' '): string
    {
        return (string) Str::minify(
            value: collect($inputsData)->reduce(
                function (string $accumulator, mixed $value, int $key) use ($withWrapper, $separator) {
                    $pattern = '-%s:%s' . $separator;
                    $accumulator .= $withWrapper ?
                        sprintf($pattern, $key, is_bool($value) ? ($value ? 'yes' : 'no') : $value) :
                        sprintf(str_replace(['[', ']'], ['', ''], $pattern), $key, $value);

                    return $accumulator;
                }
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
            ->firstWhere('project_objective_question_id', $questionType->value)?->answer ?? '';
    }
}
