<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0;

use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\Requests\Ask\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Dtos\AskPayloadDto;

/**
 * @see \Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\ChatGPT3_0
 * */
interface ChatGPT3_0
{
    public function ask(AskPayloadDto $dto): AskResponseDto;
}
