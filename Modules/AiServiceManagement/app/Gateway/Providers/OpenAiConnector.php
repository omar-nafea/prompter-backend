<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Providers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Modules\AiServiceManagement\app\Enums\AiModelProvider;
use Modules\AiServiceManagement\app\Gateway\Concerns\ParsesAiTextResponse;
use Modules\AiServiceManagement\app\Gateway\Contracts\AiProviderConnector;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Models\AiModel;

final class OpenAiConnector implements AiProviderConnector
{
    use ParsesAiTextResponse;

    private const ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    private const OPENROUTER_ENDPOINT = 'https://openrouter.ai/api/v1/chat/completions';

    public function complete(AiModel $model, string $prompt): AskResponseDto
    {
        $response = Http::withToken($model->api_key)
            ->post($this->endpoint($model), $this->payload($model, $prompt));

        $response->throw();

        /** @var array<string,mixed> $body */
        $body = $response->json();

        $content = $this->content($body);

        return $this->toResponseDto($content);
    }

    public function test(AiModel $model): array
    {
        try {
            $response = Http::withToken($model->api_key)
                ->timeout(30)
                ->post($this->endpoint($model), $this->payload($model, 'Reply with the single word: ok', 16));

            if ($response->failed()) {
                return [
                    'success' => false,
                    'message' => $model->provider->label() . ' returned HTTP ' . $response->status() . ': '
                        . ($response->json('error.message') ?? 'Request failed'),
                ];
            }

            /** @var array<string,mixed> $body */
            $body = $response->json();
            $content = $this->content($body);

            return [
                'success' => true,
                'message' => 'Connection successful',
                'response' => trim($content),
            ];
        } catch (ConnectionException) {
            return ['success' => false, 'message' => 'Could not connect to ' . $model->provider->label() . '.'];
        }
    }

    private function endpoint(AiModel $model): string
    {
        return $model->provider === AiModelProvider::OpenRouter
            ? self::OPENROUTER_ENDPOINT
            : ($model->connector_url ?? self::ENDPOINT);
    }

    /**
     * @param  array<string,mixed>  $body
     */
    private function content(array $body): string
    {
        $content = data_get($body, 'choices.0.message.content');

        return is_string($content) ? $content : '';
    }

    /**
     * @return array<string,mixed>
     */
    private function payload(AiModel $model, string $prompt, ?int $maxTokens = null): array
    {
        $payload = [
            'model' => $model->name,
            'messages' => [
                ['role' => 'user', 'content' => $prompt],
            ],
        ];
        if ($maxTokens !== null) {
            $payload['max_tokens'] = $maxTokens;
        }

        return $payload;
    }
}
