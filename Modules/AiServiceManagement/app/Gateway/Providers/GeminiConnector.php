<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Providers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Modules\AiServiceManagement\app\Gateway\Concerns\ParsesAiTextResponse;
use Modules\AiServiceManagement\app\Gateway\Contracts\AiProviderConnector;
use Modules\AiServiceManagement\app\Gateway\Dtos\AiCompletionRequest;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Models\AiModel;

final class GeminiConnector implements AiProviderConnector
{
    use ParsesAiTextResponse;

    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function complete(AiModel $model, AiCompletionRequest $request): AskResponseDto
    {
        $response = Http::withQueryParameters(['key' => $model->api_key])
            ->connectTimeout(5)
            ->timeout(60)
            ->post($this->url($model), $this->payload($request));

        $response->throw();

        /** @var array<string,mixed> $body */
        $body = $response->json();

        $content = $this->content($body);
        $usage = is_array($body['usageMetadata'] ?? null) ? $body['usageMetadata'] : [];

        return $this->toResponseDto($content, [
            'prompt_tokens' => is_int($usage['promptTokenCount'] ?? null) ? $usage['promptTokenCount'] : 0,
            'completion_tokens' => is_int($usage['candidatesTokenCount'] ?? null) ? $usage['candidatesTokenCount'] : 0,
            'total_tokens' => is_int($usage['totalTokenCount'] ?? null) ? $usage['totalTokenCount'] : 0,
        ]);
    }

    public function test(AiModel $model): array
    {
        try {
            $response = Http::withQueryParameters(['key' => $model->api_key])
                ->timeout(30)
                ->post($this->url($model), $this->payload(new AiCompletionRequest(
                    prompt: 'Reply with the single word: ok',
                    maxOutputTokens: 16,
                )));

            if ($response->failed()) {
                return [
                    'success' => false,
                    'message' => 'Gemini returned HTTP ' . $response->status() . ': '
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
            return ['success' => false, 'message' => 'Could not connect to Gemini.'];
        }
    }

    private function url(AiModel $model): string
    {
        return self::ENDPOINT . '/' . $model->name . ':generateContent';
    }

    /** @param array<string, mixed> $body */
    private function content(array $body): string
    {
        $content = data_get($body, 'candidates.0.content.parts.0.text');

        return is_string($content) ? $content : '';
    }

    /**
     * @return array<string,mixed>
     */
    private function payload(AiCompletionRequest $request): array
    {
        $payload = [
            'contents' => [
                ['parts' => [['text' => $request->prompt]]],
            ],
            'generationConfig' => [
                'temperature' => $request->temperature,
                'maxOutputTokens' => $request->maxOutputTokens,
            ],
        ];

        if (filled($request->systemPrompt)) {
            $payload['systemInstruction'] = ['parts' => [['text' => $request->systemPrompt]]];
        }
        if ($request->responseSchema !== null) {
            $payload['generationConfig']['responseMimeType'] = 'application/json';
            $payload['generationConfig']['responseSchema'] = $request->responseSchema;
        }

        return $payload;
    }
}
