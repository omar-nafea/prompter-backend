<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Providers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Modules\AiServiceManagement\app\Gateway\Concerns\ParsesAiTextResponse;
use Modules\AiServiceManagement\app\Gateway\Contracts\AiProviderConnector;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Models\AiModel;

final class GeminiConnector implements AiProviderConnector
{
    use ParsesAiTextResponse;

    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function complete(AiModel $model, string $prompt): AskResponseDto
    {
        $response = Http::withQueryParameters(['key' => $model->api_key])
            ->post($this->url($model), $this->payload($prompt));

        $response->throw();

        /** @var array<string,mixed> $body */
        $body = $response->json();

        /** @var string $content */
        $content = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';

        return $this->toResponseDto($content);
    }

    public function test(AiModel $model): array
    {
        try {
            $response = Http::withQueryParameters(['key' => $model->api_key])
                ->timeout(30)
                ->post($this->url($model), $this->payload('Reply with the single word: ok'));

            if ($response->failed()) {
                return [
                    'success' => false,
                    'message' => 'Gemini returned HTTP ' . $response->status() . ': '
                        . ($response->json('error.message') ?? 'Request failed'),
                ];
            }

            /** @var array<string,mixed> $body */
            $body = $response->json();
            /** @var string $content */
            $content = $body['candidates'][0]['content']['parts'][0]['text'] ?? '';

            return [
                'success' => true,
                'message' => 'Connection successful',
                'response' => trim((string) $content),
            ];
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Could not connect to Gemini.'];
        }
    }

    private function url(AiModel $model): string
    {
        return self::ENDPOINT . '/' . $model->name . ':generateContent';
    }

    /**
     * @return array<string,mixed>
     */
    private function payload(string $prompt): array
    {
        return [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
        ];
    }
}
