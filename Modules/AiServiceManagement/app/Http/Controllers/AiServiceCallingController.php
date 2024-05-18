<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Controllers;

use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\ChatGPT3_0;
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
        //        return response(['name' => $aiServiceName]);
        app()->make($mapper[$aiServiceName])->ask(
            new \Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Dtos\AskPayloadDto(
                'asdasdasd'
            )
        );

        //todo validate request response according to ai service related to project and valid project outputs
    }
}
