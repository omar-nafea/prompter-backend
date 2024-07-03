<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\Requests\Ask\Dtos;

interface AskResponseDto
{
    /**
     * @return array<string,mixed>
     */
    public function toArray(): array;

    /**
     * @return array<string,mixed>
     */
    public function data(): array;
}
