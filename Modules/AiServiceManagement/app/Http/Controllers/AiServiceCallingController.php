<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Controllers;

use Illuminate\Database\Eloquent\Collection;
use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\ChatGPT3_0;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\ChatGPT3_0Connector;
use Modules\AiServiceManagement\app\Http\Requests\AskAiServiceRequest;
use Modules\ProjectManagement\app\Models\Project;

class AiServiceCallingController
{
    public function ask(AskAiServiceRequest $request)
    {
        //        $projectApiKey = request()->header('X-Api-Key');
        //        dd($projectApiKey);
        //todo authenticate using api key
        //todo ensure project status is active and subscribed and not quota exceeded
        //todo validate request body according to ai service related to project and valid project inputs

        $aiServiceName = $request->project->aiService->name;
        $mapper = [
            'GPT 3.5' => ChatGPT3_0::class,
        ];
        $service = ChatGPT3_0Connector::class; // $mapper[$aiServiceName] //3.5
        dd($this->getPrompt($request->project, $request->validated()));
        //        return response(['name' => $aiServiceName]);
        app()->make($service)->ask(
            new \Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Dtos\AskPayloadDto(
                $this->getPrompt()
            )
        );

        //todo validate request response according to ai service related to project and valid project outputs
    }

    protected function getPrompt(Project $project, $inputsData)
    {
        $project->background = 'Given the following customer data and context for Tawfeer Market, generate a personalized marketing message strategy in JSON format';
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
            $pattern = '[%s] %s' . $separator;
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
            $pattern = '[%s] %s' . $separator;
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
