<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Dtos;

use App\Dtos\BaseDto;
use JsonException;
use Modules\AiServiceManagement\app\Gateway\Contracts\ChatGPT3_0\Requests\Ask\Dtos\AskResponseDto as AskResponseDtoContract;

final class AskResponseDto extends BaseDto implements AskResponseDtoContract
{
    /**
     * @param array<string, mixed> $data
     * @param string|null $rawResponse
     */
    public function __construct(
        public ?array $data = null,
        public ?string $rawResponse = null,
    ) {
        $this->data ??= [];
    }

    /**
     * @param array{
     *      data: array<string, mixed>,
     *     raw_response: array<string, mixed>,
     * } $response
     * @throws JsonException
     */
    public static function fromResponse(array $response): self
    {
        return new self(
            data: $response['data'],
            rawResponse: json_encode($response['raw_response'], JSON_THROW_ON_ERROR),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return $this->data; // @phpstan-ignore-line
    }
}
