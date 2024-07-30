<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Actions\AiService;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Modules\AiServiceManagement\app\Dtos\AskAiServiceDto;
use Modules\AiServiceManagement\app\Events\AiCallRequestFailed;
use Modules\AiServiceManagement\app\Events\AiCallRequestPrepared;
use Modules\AiServiceManagement\app\Events\AiCallRequestSent;
use Modules\AiServiceManagement\app\Events\AiCallRequestStarted;
use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\ChatGPT3_0;
use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\Requests\Ask\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Dtos\AskPayloadDto;
use Modules\ProjectManagement\app\Models\Project;

final class AskAiServiceAction
{
    public function __construct(
        protected BuildAiAskPromptAction $buildAiAskPromptAction,
        protected Container $app,
    ) {}

    /**
     * @return mixed[]
     *
     * @throws BindingResolutionException
     */
    public function execute(AskAiServiceDto $dto): array
    {
        //todo fire start event
        event(
            new AiCallRequestStarted(
                requestUuid: (string) $dto->requestUuid,
                requestBody: $dto->data,
                aiServiceName: $dto->project->aiService->name,
                projectId: $dto->project->id,
                integrationService: (string) config('ai-service-management.integrations.ai_service_integration'),// @phpstan-ignore-line
            )
        );
        try {
            $response = $this->handle($dto);
            event(
                new AiCallRequestSent(
                    requestUuid: (string) $dto->requestUuid,
                    response: $response->toArray()
                )
            );

            //todo fire sent event
            return $response->data();
        } catch (Exception $exception) {
            //todo fire failed event
            event(
                new AiCallRequestFailed(
                    requestUuid: (string) $dto->requestUuid,
                    error: $exception->getMessage(),
                )
            );
            throw $exception;
        }
    }

    /**
     * @throws BindingResolutionException
     * @throws Exception
     */
    protected function handle(AskAiServiceDto $dto): AskResponseDto
    {
        /** @var ChatGPT3_0 $serviceClass */
        $serviceClass = $this->app->make(
            abstract: $this->getServiceClass(
                aiServiceName: $dto->project->aiService->name
            )
        );

        $prompt = $this->buildAiAskPromptAction->execute(project: $dto->project, inputsData: $dto->data);
        event(
            new AiCallRequestPrepared(
                requestUuid: (string) $dto->requestUuid,
                prompt: $prompt,
                aiConnector: str(get_class($serviceClass))->after('Gateway\Integerations\\')->toString()
            )
        );

        return $serviceClass->ask(
            dto: new AskPayloadDto(
                prompt: $prompt
            )
        );
        //todo validate request response according to ai service related to project and valid project outputs
    }

    protected function getServiceClass(string $aiServiceName): string
    {
        return match ($aiServiceName) {
            'GPT 3.5' => ChatGPT3_0::class,
            'GPT 4.0' => ChatGPT3_0::class,
            default => throw new Exception('invalid ai service name')
        };
    }
}
