<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0;

use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\Requests\Ask\Dtos\AskPayloadDto;
use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\Requests\Ask\Dtos\AskResponseDto;

interface ChatGPT3_0
{
    public function ask(AskPayloadDto $dto): AskResponseDto;
}
