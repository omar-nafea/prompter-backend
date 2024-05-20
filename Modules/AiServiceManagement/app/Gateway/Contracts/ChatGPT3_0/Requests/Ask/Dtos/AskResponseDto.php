<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\Requests\Ask\Dtos;

interface AskResponseDto
{
    public function toArray(): array;

    public function data(): array;
}
