<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Providers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Modules\AiServiceManagement\app\Enums\AiModelProvider;
use Modules\AiServiceManagement\app\Gateway\Concerns\ParsesAiTextResponse;
use Modules\AiServiceManagement\app\Gateway\Contracts\AiProviderConnector;
use Modules\AiServiceManagement\app\Gateway\Dtos\AiCompletionRequest;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Models\AiModel;
use Throwable;

final class OpenAiConnector implements AiProviderConnector
{
    use ParsesAiTextResponse;

    private const ENDPOINT = 'https://api.openai.com/v1/chat/completions';

    private const OPENROUTER_ENDPOINT = 'https://openrouter.ai/api/v1/chat/completions';

    public function complete(AiModel $model, AiCompletionRequest $request): AskResponseDto
    {
        $response = Http::withToken($model->api_key)
            ->acceptJson()
            ->connectTimeout(5)
            ->timeout(60)
            ->retry(
                2,
                250,
                static fn (Throwable $exception): bool => $exception instanceof ConnectionException
                    || ($exception instanceof RequestException
                        && ($exception->response->status() === 429 || $exception->response->serverError())),
                throw: false,
            )
            ->post($this->endpoint($model), $this->payload($model, $request));

        $response->throw();

        /** @var array<string,mixed> $body */
        $body = $response->json();

        $content = $this->content($body);

        return $this->toResponseDto($content, is_array($body['usage'] ?? null) ? $body['usage'] : []);
    }

    public function test(AiModel $model): array
    {
        try {
            $response = Http::withToken($model->api_key)
                ->timeout(30)
                ->post($this->endpoint($model), $this->payload($model, new AiCompletionRequest(
                    prompt: 'Reply with the single word: ok',
                    maxOutputTokens: 16,
                )));

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
    private function payload(AiModel $model, AiCompletionRequest $request): array
    {
        $messages = [];
        if (filled($request->systemPrompt)) {
            $messages[] = ['role' => 'system', 'content' => $request->systemPrompt];
        }
        $messages[] = ['role' => 'user', 'content' => $request->prompt];

        $payload = [
            'model' => $model->name,
            'temperature' => $request->temperature,
            'messages' => $messages,
        ];

        $tokenParameter = $model->provider === AiModelProvider::OpenAi
            ? 'max_completion_tokens'
            : 'max_tokens';
        $payload[$tokenParameter] = $request->maxOutputTokens;

        if ($request->responseSchema !== null) {
            $payload['response_format'] = [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => $request->responseSchemaName,
                    'strict' => true,
                    'schema' => $request->responseSchema,
                ],
            ];
        }

        return $payload;
    }
}
