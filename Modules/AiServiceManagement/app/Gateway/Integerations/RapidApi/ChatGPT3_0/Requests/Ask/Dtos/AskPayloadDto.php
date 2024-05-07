<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Dtos;

use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\Requests\Ask\Dtos\AskPayloadDto as AskPayloadDtoContract;
use Spatie\LaravelData\Data;

class AskPayloadDto extends Data implements AskPayloadDtoContract
{
    public function __construct(
        public string $prompt
    ) {}
}
