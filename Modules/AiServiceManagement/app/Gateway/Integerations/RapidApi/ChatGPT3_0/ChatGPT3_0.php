<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0;

use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\ChatGPT3_0 as ChatGPT3_0Contract;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\AskRequest;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Dtos\AskPayloadDto;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Dtos\AskResponseDto;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class ChatGPT3_0 implements ChatGPT3_0Contract
{
    public function __construct(
        protected ChatGPT3_0Connector $connector,
    ) {}

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function ask(AskPayloadDto $dto): AskResponseDto
    {
        /** @var AskRequest $request */
        $request = app(AskRequest::class);
        /** @var array<int,mixed>$body */
        $body = $request->body()->get('body');
        $body[] = [
            'content' => $dto->prompt,
            'role' => 'user',
        ];
        $request->body()->set($body);
        //        dd($request->body()->all());

        $this->fake();

        /** @var AskResponseDto */
        return $this->connector->send($request)->dtoOrFail();
    }

    protected function fake(): void
    {
        $mockClient = new MockClient([
            AskRequest::class => MockResponse::make(body: '{"text":"```json\n{\n \"messageType\": \"fake response 2xCoupon Offers on products\",\n \"messageContent\": \"Enjoy discounts on selected products this Spring at Tawfeer Market!\",\n \"communicationChannels\": [\"Email\", \"SMS\", \"Push Notification\", \"WhatsApp\"],\n \"messageHeader\": \"Spring Savings at Tawfeer Market!\",\n \"sendTiming\": {\n \"Email\": \"10:00\",\n \"SMS\": \"15:00\",\n \"Push Notification\": \"17:30\",\n \"WhatsApp\": \"12:00\"\n }\n}\n```","finish_reason":"stop","model":"gpt-3.5-turbo-030","server":"backup-K"}', status: 200),
        ]);
        $this->connector->withMockClient($mockClient);
    }
}
