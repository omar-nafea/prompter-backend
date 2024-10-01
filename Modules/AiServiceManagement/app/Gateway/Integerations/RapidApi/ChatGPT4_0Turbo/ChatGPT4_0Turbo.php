<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT4_0Turbo;

use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT4_0Turbo\ChatGPT4_0Turbo as ChatGPT4_0TurboContract;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskPayloadDto;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT4_0Turbo\Requests\Ask\AskRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Storage;

final class ChatGPT4_0Turbo implements ChatGPT4_0TurboContract
{
    public function __construct(
        protected ChatGPT4_0TurboConnector $connector,
    ) {}

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function ask(AskPayloadDto $dto): AskResponseDto
    {
        /** @var AskRequest $request */
        $request = app(AskRequest::class);

        $body = $request->body()->get();
        $body['messages'][] = [
            'content' => $dto->prompt,
            'role' => 'user',
        ];
        $request->body()->set($body);
        $request->setMaxTokens($dto->project->max_tokens);
        $request->setTemperature($dto->project->details->ai_temperature);

        if (config('ai-service-management.integrations.fake_response') || config('ai-service-management.integrations.rapid_api.ChatGPT4_0Turbo.fake_response')) {
            $this->fake();
        }

        /** @var AskResponseDto */
        return $this->connector->send($request)->dtoOrFail();
    }

    protected function fake(): void
    {
        $mockClient = new MockClient([
            AskRequest::class => MockResponse::make(body: Storage::disk('local')->get('samples/responses/cheapest-gpt-4-turbo-gpt-4-vision-chatgpt-openai-ai-api.p.rapidapi'), status: 200),
        ]);
        $this->connector->withMockClient($mockClient);
    }
}
