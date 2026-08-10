<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Dtos;

use App\Dtos\BaseDto;
use JsonException;

final class AskResponseDto extends BaseDto
{
    /**
     * @param  array<mixed>  $data
     */
    public function __construct(
        public ?array $data = null,
        public ?string $rawResponse = null,
        /** @var array{prompt_tokens: int, completion_tokens: int, total_tokens: int} */
        public array $usage = [
            'prompt_tokens' => 0,
            'completion_tokens' => 0,
            'total_tokens' => 0,
        ],
    ) {
        $this->data ??= [];
    }

    /**
     * @param array{
     *      data: array<mixed>,
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
     * @return array<mixed>
     */
    public function data(): array
    {
        return $this->data; // @phpstan-ignore-line
    }
}
