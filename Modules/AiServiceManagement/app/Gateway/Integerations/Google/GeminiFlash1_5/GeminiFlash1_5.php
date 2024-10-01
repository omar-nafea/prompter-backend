<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\Google\GeminiFlash1_5;

use Modules\AiServiceManagement\app\Gateway\Contracts\GeminiFlash1_5\GeminiFlash1_5 as GeminiFlash1_5Contract;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskPayloadDto;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Gateway\Integerations\Google\GeminiFlash1_5\Requests\AskRequest;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

final class GeminiFlash1_5 implements GeminiFlash1_5Contract
{
    public function __construct(
        protected GeminiFlash1_5Connector $connector,
    ) {}

    /**
     * @throws FatalRequestException
     * @throws RequestException
     */
    public function ask(AskPayloadDto $dto): AskResponseDto
    {
        /** @var AskRequest $request */
        $request = app(AskRequest::class);
        //        dd($request->body());
        /** @var array<int,mixed>$body */
        $body = $request->body()->get();
        $body['contents'][0]['parts']['text'] = $dto->prompt;
        $request->setTemperature($dto->project->details->ai_temperature);
        //        dd($body);
        //        dd($request->body()->get(),$body);
        //        $body[] = [
        //            'content' => $dto->prompt,
        //            'role' => 'user',
        //        ];
        $request->body()->set($body);
        //        dd($request->body()->all());
        if (config('ai-service-management.integrations.fake_response') || config('ai-service-management.integrations.google.Gemini_1_5_Flash.fake_response')) {
            $this->fake();
        }

        /** @var AskResponseDto */
        return $this->connector->send($request)->dtoOrFail();
    }

    protected function fake(): void
    {
        $mockClient = new MockClient([
            '*' => MockResponse::make(body: '{
    "candidates": [
        {
            "content": {
                "parts": [
                    {
                        "text": "{\"messageType\": \"Coupon Offers on products\", \"messageContent\": \"Get 20EGP off your next order of 500EGP or more! Use code FreeD at checkout. Enjoy your shopping!\", \"communicationChannels\": [\"Push Notification\", \"SMS\", \"Email\", \"WhatsApp\"], \"messageHeader\": \"Special Offer for You!\", \"sendTiming\": {\"Push Notification\": \"10:00\", \"SMS\": \"12:00\", \"Email\": \"14:00\", \"WhatsApp\": \"16:00\"}}\n"
                    }
                ],
                "role": "model"
            },
            "finishReason": "STOP",
            "index": 0,
            "safetyRatings": [
                {
                    "category": "HARM_CATEGORY_SEXUALLY_EXPLICIT",
                    "probability": "NEGLIGIBLE"
                },
                {
                    "category": "HARM_CATEGORY_HATE_SPEECH",
                    "probability": "NEGLIGIBLE"
                },
                {
                    "category": "HARM_CATEGORY_HARASSMENT",
                    "probability": "NEGLIGIBLE"
                },
                {
                    "category": "HARM_CATEGORY_DANGEROUS_CONTENT",
                    "probability": "NEGLIGIBLE"
                }
            ]
        }
    ],
    "usageMetadata": {
        "promptTokenCount": 885,
        "candidatesTokenCount": 118,
        "totalTokenCount": 1003
    }
}', status: 200),
        ]);
        $this->connector->withMockClient($mockClient);
    }
}
