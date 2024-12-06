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
use Storage;

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
            '*' => MockResponse::make(body: Storage::disk('local')->get('samples/responses/gemini-flash1_5')),
        ]);
        $this->connector->withMockClient($mockClient);
    }
}
