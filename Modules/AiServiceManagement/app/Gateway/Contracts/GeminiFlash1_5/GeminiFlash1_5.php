<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Contracts\GeminiFlash1_5;

use Modules\AiServiceManagement\app\Gateway\Dtos\AskPayloadDto;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;

interface GeminiFlash1_5
{
    public function ask(AskPayloadDto $dto): AskResponseDto;
}
