<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT4_0;

use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT4_0\ChatGPT4_0 as ChatGPT4_0Contract;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskPayloadDto;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT4_0\Requests\Ask\AskRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Storage;

final class ChatGPT4_0 implements ChatGPT4_0Contract
{
    public function __construct(
        protected ChatGPT4_0Connector $connector,
    ) {}

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function ask(AskPayloadDto $dto): AskResponseDto
    {
        /** @var AskRequest $request */
        $request = app(AskRequest::class);

        $request->body()->add(
            name : 'prompt',
            contents : $dto->prompt,
        );

        if (config('ai-service-management.integrations.fake_response') || config('ai-service-management.integrations.rapid_api.ChatGPT4_0.fake_response')) {
            $this->fake();
        }

        /** @var AskResponseDto */
        return $this->connector->send($request)->dtoOrFail();
    }

    protected function fake(): void
    {
        $mockClient = new MockClient([
            AskRequest::class => MockResponse::make(body: Storage::disk('local')->get('samples/responses/chat-gpt-44.p.rapidapi'), status: 200),
        ]);
        $this->connector->withMockClient($mockClient);
    }
}
