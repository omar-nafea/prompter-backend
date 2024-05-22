<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Actions\AiService;

use Illuminate\Database\Eloquent\Collection;
use Modules\ProjectManagement\app\Models\Project;

final class BuildAiAskPromptAction
{
    public function execute(Project $project, $inputsData)
    {
        $project->background = '';
        $project->expected_outcome = 'Given the following customer data and context for Tawfeer Market, generate a personalized marketing message strategy';
        //        dd($project->expected_outcome);
        $prompt = '[BACKGROUND]. [EXPECTED OUTCOMES] in [FORMAT] format. The output should specify the [LIST OF OUTPUTS WITH DESCRIPTION OF EACH IF AVAILABLE]. Consider the [LIST OF INPUTS WITH DESCRIPTION OF EACH IF AVAILABLE] to tailor the response accordingly.  The output should be [FORMAT] only and nothing else in the message. [INPUTS WITH VALUES]. Application display Language: [LANGUAGE].Generate a [FORMAT] output that includes the following: [OUTPUTS WITH DESCRIPTION]. The output should be (Maximum [OUTPUT MAXIMUM LENGTH])';
        $string = str($prompt)
            ->replace('[BACKGROUND]', $project->background)
            ->replace('[EXPECTED OUTCOMES]', $project->expected_outcome)
            ->replace('[FORMAT]', 'json') // todo add format to project table
            ->replace(
                '[LIST OF OUTPUTS WITH DESCRIPTION OF EACH IF AVAILABLE]',
                $this->prepareOutputsWithDescription(projectOutputs: $project->outputs, separator: ',')
            )
            ->replace(
                '[LIST OF INPUTS WITH DESCRIPTION OF EACH IF AVAILABLE]',
                $this->prepareInputsWithDescription($project->inputs)
            )
            ->replace('[FORMAT]', 'json')// todo add format to project table
            ->replace(
                '[INPUTS WITH VALUES]',
                $this->prepareInputsWithValues($inputsData)
            )
            ->replace('[LANGUAGE]', 'english')// todo add language to project table
            ->replace(
                '[OUTPUTS WITH DESCRIPTION]',
                $this->prepareOutputsWithDescription($project->outputs, true)
            )
            ->replace('[OUTPUT MAXIMUM LENGTH]', 1000) // todo add output maximum to project table
            ->toString();

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
     * @param  Collection<int, \Modules\ProjectManagement\app\Models\ProjectOutput>  $projectOutputs
     */
    protected function prepareOutputsWithDescription(Collection $projectOutputs, bool $withWrapper = false, string $separator = ';'): string
    {
        return (string) $projectOutputs->reduce(function ($accumulator, $projectOutput) use ($withWrapper, $separator) {
            $pattern = '[%s]:%s' . $separator . ' ';
            $accumulator .= $withWrapper ?
                sprintf($pattern, $projectOutput->name, $projectOutput->description) :
                sprintf(str_replace(['[', ']'], ['', ''], $pattern), $projectOutput->name, $projectOutput->description);

            //            $accumulator .= sprintf('[%s], %s;', $projectOutput->name, $projectOutput->description);
            return $accumulator;
        }, '');
    }

    protected function prepareInputsWithDescription(Collection $projectInputs, bool $withWrapper = false, string $separator = ';'): string
    {
        return (string) $projectInputs->reduce(function ($accumulator, $projectInput) use ($withWrapper, $separator) {
            $pattern = '[%s]: %s' . $separator;
            $accumulator .= $withWrapper ?
                sprintf($pattern, $projectInput->name, $projectInput->description) :
                sprintf(str_replace(['[', ']'], ['', ''], $pattern), $projectInput->name, $projectInput->description);

            return $accumulator;
        }, '');
    }

    protected function prepareInputsWithValues($inputsData, bool $withWrapper = false, string $separator = ' '): string
    {
        return (string) collect($inputsData)->reduce(function ($accumulator, $value, $key) use ($withWrapper, $separator) {
            $pattern = '-%s:%s' . $separator;
            $accumulator .= $withWrapper ?
                sprintf($pattern, $key, is_bool($value) ? ($value ? 'yes' : 'no') : $value) :
                sprintf(str_replace(['[', ']'], ['', ''], $pattern), $key, $value);

            return $accumulator;
        });
    }
}
