<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Actions\AiService;

use Exception;
use Modules\AiServiceManagement\app\Dtos\AskAiServiceDto;
use Modules\AiServiceManagement\app\Events\AiCallRequestFailed;
use Modules\AiServiceManagement\app\Events\AiCallRequestPrepared;
use Modules\AiServiceManagement\app\Events\AiCallRequestSent;
use Modules\AiServiceManagement\app\Events\AiCallRequestStarted;
use Modules\AiServiceManagement\app\Gateway\AiProviderResolver;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Models\AiModel;

final class AskAiServiceAction
{
    public function __construct(
        protected BuildAiAskPromptAction $buildAiAskPromptAction,
        protected AiProviderResolver $resolver,
    ) {}

    /**
     * @return mixed[]
     */
    public function execute(AskAiServiceDto $dto): array
    {
        $model = AiModel::query()->firstOrFail();
        event(
            new AiCallRequestStarted(
                requestUuid: (string) $dto->requestUuid,
                requestBody: $dto->data,
                aiServiceName: $model->alias,
                projectId: $dto->project->id,
            )
        );
        try {
            $response = $this->handle($dto, $model);
            event(
                new AiCallRequestSent(
                    requestUuid: (string) $dto->requestUuid,
                    response: $response->toArray()
                )
            );

            return [
                'request_uuid' => $dto->requestUuid,
                ...$response->data(),
            ];
        } catch (Exception $exception) {
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
     * @throws Exception
     */
    protected function handle(AskAiServiceDto $dto, AiModel $model): AskResponseDto
    {
        $prompt = $this->buildAiAskPromptAction->execute(project: $dto->project, inputsData: $dto->data);
        $connector = $this->resolver->for($model->provider);

        event(
            new AiCallRequestPrepared(
                requestUuid: (string) $dto->requestUuid,
                //                prompt: $prompt,
                aiConnector: $model->name,
                integrationService: $model->provider->label(),
            )
        );

        return $connector->complete($model, $prompt);
        //todo validate request response according to ai service related to project and valid project outputs
    }
}
