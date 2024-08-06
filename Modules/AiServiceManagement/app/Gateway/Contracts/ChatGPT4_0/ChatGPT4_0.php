<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT4_0;

use Modules\AiServiceManagement\app\Gateway\Dtos\AskPayloadDto;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;

/**
 * @see \Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT4_0\ChatGPT4_0
 * */
interface ChatGPT4_0
{
    public function ask(AskPayloadDto $dto): AskResponseDto;
}
