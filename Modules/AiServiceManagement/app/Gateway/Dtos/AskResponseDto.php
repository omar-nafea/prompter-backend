<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Dtos;

use App\Dtos\BaseDto;
use JsonException;

final class AskResponseDto extends BaseDto
{
    /**
     * @param  array<string, mixed>  $data
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
     *
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
