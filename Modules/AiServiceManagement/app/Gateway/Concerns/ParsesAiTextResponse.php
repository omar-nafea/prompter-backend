<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Concerns;

use JsonException;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;

trait ParsesAiTextResponse
{
    /**
     * @param  array{prompt_tokens?: int, completion_tokens?: int, total_tokens?: int}  $usage
     */
    protected function toResponseDto(string $text, array $usage = []): AskResponseDto
    {
        $data = $this->extractJson($text);

        return new AskResponseDto(
            data: $data ?? ['message' => $text],
            rawResponse: $text,
            usage: [
                'prompt_tokens' => max(0, (int) ($usage['prompt_tokens'] ?? 0)),
                'completion_tokens' => max(0, (int) ($usage['completion_tokens'] ?? 0)),
                'total_tokens' => max(0, (int) ($usage['total_tokens'] ?? 0)),
            ],
        );
    }

    /**
     * @return array<mixed>|null
     */
    protected function extractJson(string $text): ?array
    {
        if (preg_match('/```json\s*(.+?)\s*```/s', $text, $matches)) {
            $candidate = $matches[1];
        } elseif (preg_match('/```\s*(.+?)\s*```/s', $text, $matches)) {
            $candidate = $matches[1];
        } else {
            $candidate = trim($text);
        }

        try {
            $decoded = json_decode($candidate, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return null;
        }

        return is_array($decoded) ? $decoded : null;
    }
}
