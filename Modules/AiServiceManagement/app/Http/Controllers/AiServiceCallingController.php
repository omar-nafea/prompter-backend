<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Controllers;

use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\ChatGPT3_0;
use Modules\ProjectManagement\app\Models\Project;

class AiServiceCallingController
{
    public function ask($project)
    {
        //todo authenticate using api key
        //todo ensure project status is active and subscribed and not quota exceeded
        //todo validate request body according to ai service related to project and valid project inputs

        $aiServiceName = Project::findOrFail($project)->aiService->name;
        $mapper = [
            'chat_gpt3' => ChatGPT3_0::class,
        ];
        app()->make($aiServiceName)->ask(request()->body());

        //todo validate request response according to ai service related to project and valid project outputs
    }
}
