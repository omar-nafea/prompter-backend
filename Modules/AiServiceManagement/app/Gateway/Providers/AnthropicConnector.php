<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Providers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Modules\AiServiceManagement\app\Gateway\Concerns\ParsesAiTextResponse;
use Modules\AiServiceManagement\app\Gateway\Contracts\AiProviderConnector;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Models\AiModel;

final class AnthropicConnector implements AiProviderConnector
{
    use ParsesAiTextResponse;

    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

    private const VERSION = '2023-06-01';

    public function complete(AiModel $model, string $prompt): AskResponseDto
    {
        $response = $this->client($model)
            ->post(self::ENDPOINT, $this->payload($model, $prompt, 1024));

        $response->throw();

        /** @var array<string,mixed> $body */
        $body = $response->json();

        /** @var string $content */
        $content = collect($body['content'] ?? [])->pluck('text')->implode("\n");

        return $this->toResponseDto($content);
    }

    public function test(AiModel $model): array
    {
        try {
            $response = $this->client($model)
                ->timeout(30)
                ->post(self::ENDPOINT, $this->payload($model, 'Reply with the single word: ok', 16));

            if ($response->failed()) {
                return [
                    'success' => false,
                    'message' => 'Anthropic returned HTTP ' . $response->status() . ': '
                        . ($response->json('error.message') ?? 'Request failed'),
                ];
            }

            /** @var array<string,mixed> $body */
            $body = $response->json();
            /** @var string $content */
            $content = collect($body['content'] ?? [])->pluck('text')->implode("\n");

            return [
                'success' => true,
                'message' => 'Connection successful',
                'response' => trim((string) $content),
            ];
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Could not connect to Anthropic.'];
        }
    }

    private function client(AiModel $model)
    {
        return Http::withHeaders([
            'x-api-key' => $model->api_key,
            'anthropic-version' => self::VERSION,
        ]);
    }

    /**
     * @return array<string,mixed>
     */
    private function payload(AiModel $model, string $prompt, int $maxTokens): array
    {
        return [
            'model' => $model->name,
            'max_tokens' => $maxTokens,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];
    }
}
