<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0;

use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\ChatGPT3_0 as ChatGPT3_0Contract;
use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\Requests\Ask\Dtos\AskPayloadDto;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\AskRequest;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Dtos\AskResponseDto;

class ChatGPT3_0 implements ChatGPT3_0Contract
{
    public function __construct(
        protected ChatGPT3_0Connector $connector,
    ) {}

    public function ask(AskPayloadDto $dto): AskResponseDto
    {
        $request = app(AskRequest::class);
        $request->body()->merge($dto->toArray());

        return $this->connector->send($request)->dtoOrFail();
    }
}
