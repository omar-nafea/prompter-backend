<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Dtos;

use Spatie\LaravelData\Data;

final class AskPayloadDto extends Data
{
    public function __construct(
        public string $prompt
    ) {}
}
