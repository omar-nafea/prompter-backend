<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\Google\GeminiFlash1_5\Requests;

use JsonException;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Actions\ConvertTextResponseToJsonAction;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Exceptions\FailedResponseException;
use Override;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Plugins\HasTimeout;
use Symfony\Component\HttpFoundation\Response as ResponseStatus;
use Throwable;

final class AskRequest extends Request implements HasBody
{
    use HasJsonBody;
    use HasTimeout;

    protected int $connectTimeout = 60;

    protected int $requestTimeout = 120;

    protected Method $method = Method::POST;

    public function __construct(
        protected ConvertTextResponseToJsonAction $convertTextResponseToJsonAction
    ) {}

    #[Override]
    public function resolveEndpoint(): string
    {
        return '/gemini-1.5-flash:generateContent';
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaultBody(): array
    {
        return [
            'generationConfig' => [
                'responseMimeType' => 'application/json',
            ],
            'safetySettings' => [],
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [],
                ],
            ],
        ];
    }

    /**
     * @throws JsonException
     */
    #[Override]
    public function createDtoFromResponse(Response $response): AskResponseDto
    {
        //        dd($response->json());
        $res['raw_response'] = $response->json();
        //info(json_encode($response->json()));
        /** @var string $textResponse */
        $textResponse = $response->json()['candidates'][0]['content']['parts'][0]['text'] ?? ''; //@phpstan-ignore-line
        $res['data'] = $this->convertTextResponseToJsonAction->execute(
            textResponse: $textResponse
        );

        return AskResponseDto::fromResponse($res);
    }

    /**
     * @throws JsonException
     */
    #[Override]
    public function hasRequestFailed(Response $response): ?bool
    {
        return $response->status() !== ResponseStatus::HTTP_OK || ! $response->json('candidates');
        //todo customize failed response
    }

    /**
     * @throws JsonException
     */
    #[Override]
    public function getRequestException(Response $response, ?Throwable $senderException): ?Throwable
    {
        info(json_encode($response->json()));

        return FailedResponseException::failedAskResponse(
            (bool) config('ai-service-management.debug_enabled') ? $response->json() :
                ['message' => 'Something went wrong. Please try again later.']
        );
    }
}
