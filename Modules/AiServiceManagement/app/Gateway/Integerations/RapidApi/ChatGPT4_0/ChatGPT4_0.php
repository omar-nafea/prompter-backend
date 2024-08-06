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
        /** @var array<int,mixed>$body */
        $body = $request->body()->get();
        $body['messages'][] = [
            'content' => $dto->prompt,
            'role' => 'user',
        ];
        $request->body()->set($body);
        //        dd($request->body()->all());

        if (config('ai-service-management.integrations.fake_response') || config('ai-service-management.integrations.rapid_api.ChatGPT4_0.fake_response')) {
            $this->fake();
        }

        /** @var AskResponseDto */
        return $this->connector->send($request)->dtoOrFail();
    }

    protected function fake(): void
    {
        $mockClient = new MockClient([
            AskRequest::class => MockResponse::make(body: '{"created":1722974688,"usage":{"completion_tokens":271,"prompt_tokens":931,"total_tokens":1202},"model":"gpt-4o","id":"chatcmpl-rLvJLJRLV26ssjXopMaMVTnMuJn5T","choices":[{"finish_reason":"stop","index":0,"message":{"role":"assistant","content":"Here is the personalized marketing message strategy for Rawda Elshahawy, based on the provided data and context:\n\n```json\n{\n  \"messageType\": \"Coupon Offers\",\n  \"messageContent\": \"Enjoy 20EGP off with code FreeD on your next order! Check out discounted Capucci and more fresh deals.\",\n  \"communicationChannels\": [\"Email\", \"Push Notification\", \"WhatsApp\"],\n  \"messageHeader\": \"Exclusive Savings Just for You!\",\n  \"sendTiming\": {\n    \"Email\": \"08:00\",\n    \"Push Notification\": \"09:30\",\n    \"WhatsApp\": \"10:00\"\n  }\n}\n```\n\n### Explanation:\n- **Message Type:** \"Coupon Offers\" is selected to reward Rawda as a frequent buyer and encourage further engagement.\n- **Message Content:** Tailored to highlight the ongoing discounts and the general offer code \"FreeD\", keeping the message under the 142-character limit.\n- **Communication Channels:** Email, Push Notification, and WhatsApp are chosen for broader reach, considering Rawda\u2019s device type and usual engagement patterns.\n- **Message Header:** \"Exclusive Savings Just for You!\" is crafted to grab attention and foster a sense of exclusivity.\n- **Send Timing:** Morning times are selected for each channel to align with typical user engagement peaks and increase the chances of interaction."}}],"object":"chat.completion"}', status: 200),
        ]);
        $this->connector->withMockClient($mockClient);
    }
}
