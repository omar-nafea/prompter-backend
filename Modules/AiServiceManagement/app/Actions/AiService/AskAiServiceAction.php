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
use Modules\AiServiceManagement\app\Gateway\Dtos\AiCompletionRequest;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Models\AiModel;

final class AskAiServiceAction
{
    public function __construct(
        protected BuildAiAskPromptAction $buildAiAskPromptAction,
        protected ResolveAiResponseSchemaAction $resolveAiResponseSchemaAction,
        protected AiProviderResolver $resolver,
    ) {}

    /**
     * @return mixed[]
     */
    public function execute(AskAiServiceDto $dto): array
    {
        $model = $dto->project->aiModel
            ?? $dto->project->aiModel()->firstOrFail();
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

            $responseData = $response->data();
            if (array_is_list($responseData)) {
                throw new Exception('The AI response must be a JSON object, not a top-level array.');
            }

            return [
                'request_uuid' => $dto->requestUuid,
                ...$responseData,
                '_meta' => ['usage' => $response->usage],
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
        $details = $dto->project->loadMissing('details')->details;

        event(
            new AiCallRequestPrepared(
                requestUuid: (string) $dto->requestUuid,
                aiConnector: $model->name,
                integrationService: $model->provider->label(),
            )
        );

        return $connector->complete($model, new AiCompletionRequest(
            prompt: $prompt,
            systemPrompt: $details?->system_prompt,
            temperature: $details?->ai_temperature ?? 0.0,
            maxOutputTokens: $details?->max_output_tokens ?? 1024,
            responseSchema: $this->resolveAiResponseSchemaAction->execute(
                $details?->response_schema,
                $dto->data,
            ),
            responseSchemaName: 'project_' . $dto->project->id . '_response',
        ));
    }
}
