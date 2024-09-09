<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT4_0Turbo;

use Modules\AiServiceManagement\app\Gateway\Dtos\AskPayloadDto;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;

/**
 * @see \Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT4_0Turbo\ChatGPT4_0Turbo
 * */
interface ChatGPT4_0Turbo
{
    public function ask(AskPayloadDto $dto): AskResponseDto;
}
