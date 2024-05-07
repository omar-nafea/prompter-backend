<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Dtos;

use App\Dtos\BaseDto;
use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\Requests\Ask\Dtos\AskResponseDto as AskResponseDtoContract;

class AskResponseDto extends BaseDto implements AskResponseDtoContract
{
    public function __construct(
        public array $body
    ) {}

    public static function fromResponse(array $response): self
    {
        return self::from($response);
    }

    public function result(): string
    {
        return $this->body['result'];
    }
}
