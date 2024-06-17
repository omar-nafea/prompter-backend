<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask;

use JsonException;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Actions\ConvertTextResponseToJsonAction;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Dtos\AskResponseDto;
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
        return '/';
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaultBody(): array
    {
        return [
            'body' => [
                [
                    'content' => "Hello! I\'m an AI assistant bot based on ChatGPT 3. How may I help you?",
                    'role' => 'system',
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
        $res['raw_response'] = $response->json();
        //info(json_encode($response->json()));
        /** @var string $textResponse */
        $textResponse = $response->json()['text'] ?? '';
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
        return $response->status() !== ResponseStatus::HTTP_OK || ! $response->json('text');
        //todo customize failed response
    }

    /**
     * @throws JsonException
     */
    #[Override]
    public function getRequestException(Response $response, ?Throwable $senderException): ?Throwable
    {
        return FailedResponseException::failedAskResponse($response->json());
    }
}
