<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Actions\AiService;

use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\ChatGPT3_0;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\ChatGPT3_0Connector;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Dtos\AskPayloadDto;
use Modules\ProjectManagement\app\Models\Project;

final class AskAiServiceAction
{
    public function __construct(
        protected BuildAiAskPromptAction $buildAiAskPromptAction
    ) {}

    public function execute($request)
    {
        $aiServiceName = $request->project->aiService->name;
        $mapper = [
            'GPT 3.5' => ChatGPT3_0::class,
            'GPT 4.0' => ChatGPT3_0::class,
        ];
        //        dd( app()->make($mapper[$aiServiceName]));
        //        $service = ChatGPT3_0Connector::class;
        $service = $mapper[$aiServiceName];
        //        dd($this->buildAiAskPromptAction->execute($request->project, $request->validated()));

        return app()->make($service)->ask(
            dto: new AskPayloadDto(
                prompt: $this->buildAiAskPromptAction->execute($request->project, $request->validated())
            )
        );
        //todo validate request response according to ai service related to project and valid project outputs
    }
}
