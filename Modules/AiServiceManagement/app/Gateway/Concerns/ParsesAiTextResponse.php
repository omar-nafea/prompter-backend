<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Concerns;

use JsonException;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;

trait ParsesAiTextResponse
{
    /**
     * Build the response DTO from the raw text returned by a provider.
     * If the text contains a fenced ```json block, its decoded payload is
     * used as the structured data; otherwise the raw text is returned as-is.
     */
    protected function toResponseDto(string $text): AskResponseDto
    {
        $data = $this->extractJson($text);

        return new AskResponseDto(
            data: $data ?? ['message' => $text],
            rawResponse: $text,
        );
    }

    /**
     * @return array<string,mixed>|null
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
