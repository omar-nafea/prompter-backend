<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Dtos;

final readonly class AiCompletionRequest
{
    /**
     * @param  array<string, mixed>|null  $responseSchema
     */
    public function __construct(
        public string $prompt,
        public ?string $systemPrompt = null,
        public float $temperature = 0.0,
        public int $maxOutputTokens = 1024,
        public ?array $responseSchema = null,
        public string $responseSchemaName = 'structured_response',
    ) {}
}
