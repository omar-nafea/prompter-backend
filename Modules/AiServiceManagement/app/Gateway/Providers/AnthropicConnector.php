<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Providers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use Modules\AiServiceManagement\app\Gateway\Concerns\ParsesAiTextResponse;
use Modules\AiServiceManagement\app\Gateway\Contracts\AiProviderConnector;
use Modules\AiServiceManagement\app\Gateway\Dtos\AiCompletionRequest;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Models\AiModel;

final class AnthropicConnector implements AiProviderConnector
{
    use ParsesAiTextResponse;

    private const ENDPOINT = 'https://api.anthropic.com/v1/messages';

    private const VERSION = '2023-06-01';

    public function complete(AiModel $model, AiCompletionRequest $request): AskResponseDto
    {
        $response = $this->client($model)
            ->connectTimeout(5)
            ->timeout(60)
            ->post(self::ENDPOINT, $this->payload($model, $request));

        $response->throw();

        /** @var array<string,mixed> $body */
        $body = $response->json();

        $content = $this->content($body);
        $usage = is_array($body['usage'] ?? null) ? $body['usage'] : [];
        $inputTokens = is_int($usage['input_tokens'] ?? null) ? $usage['input_tokens'] : 0;
        $outputTokens = is_int($usage['output_tokens'] ?? null) ? $usage['output_tokens'] : 0;

        return $this->toResponseDto($content, [
            'prompt_tokens' => $inputTokens,
            'completion_tokens' => $outputTokens,
            'total_tokens' => $inputTokens + $outputTokens,
        ]);
    }

    public function test(AiModel $model): array
    {
        try {
            $response = $this->client($model)
                ->timeout(30)
                ->post(self::ENDPOINT, $this->payload($model, new AiCompletionRequest(
                    prompt: 'Reply with the single word: ok',
                    maxOutputTokens: 16,
                )));

            if ($response->failed()) {
                return [
                    'success' => false,
                    'message' => 'Anthropic returned HTTP ' . $response->status() . ': '
                        . ($response->json('error.message') ?? 'Request failed'),
                ];
            }

            /** @var array<string,mixed> $body */
            $body = $response->json();
            $content = $this->content($body);

            return [
                'success' => true,
                'message' => 'Connection successful',
                'response' => trim((string) $content),
            ];
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Could not connect to Anthropic.'];
        }
    }

    private function client(AiModel $model): PendingRequest
    {
        return Http::withHeaders([
            'x-api-key' => $model->api_key,
            'anthropic-version' => self::VERSION,
        ]);
    }

    /** @param array<string, mixed> $body */
    private function content(array $body): string
    {
        $parts = is_array($body['content'] ?? null) ? $body['content'] : [];

        return collect($parts)->pluck('text')->filter('is_string')->implode("\n");
    }

    /**
     * @return array<string,mixed>
     */
    private function payload(AiModel $model, AiCompletionRequest $request): array
    {
        $payload = [
            'model' => $model->name,
            'max_tokens' => $request->maxOutputTokens,
            'temperature' => $request->temperature,
            'messages' => [
                ['role' => 'user', 'content' => $request->prompt],
            ],
        ];

        if (filled($request->systemPrompt)) {
            $payload['system'] = $request->systemPrompt;
        }

        return $payload;
    }
}
