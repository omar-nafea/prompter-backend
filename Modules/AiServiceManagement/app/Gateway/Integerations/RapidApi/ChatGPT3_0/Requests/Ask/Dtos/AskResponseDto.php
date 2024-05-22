<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Dtos;

use App\Dtos\BaseDto;
use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\Requests\Ask\Dtos\AskResponseDto as AskResponseDtoContract;

class AskResponseDto extends BaseDto implements AskResponseDtoContract
{
    public function __construct(
        public ?array $data = null,
        public ?string $rawResponse = null,
    ) {}

    public static function fromResponse($response): self
    {
        return new self(
            data: $response['data'],
            rawResponse: json_encode($response['raw_response']),
        );
    }

    protected static function prepareTextResponse(string $textResponse): array
    {
        $string = str($textResponse)->remove('```')->remove('json')->trim()->toString();
        if (! json_validate($string)) {
            //todo add exception here
            dd('not json');
        }

        return json_decode($string, true);
    }

    public function data(): array
    {
        return $this->data;
    }
}
