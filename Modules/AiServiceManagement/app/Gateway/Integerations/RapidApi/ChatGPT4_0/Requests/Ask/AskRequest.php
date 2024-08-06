<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT4_0\Requests\Ask;

use JsonException;
use Modules\AiServiceManagement\app\Gateway\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT4_0\Requests\Ask\Actions\ConvertTextResponseToJsonAction;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT4_0\Requests\Ask\Exceptions\FailedResponseException;
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
        return '/chat/completions';
    }

    /**
     * @return array<string,mixed>
     */
    protected function defaultBody(): array
    {
        return [
            'model' => 'gpt-4o',
            'messages' => [],
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
        $textResponse = $response->json()['choices'][0]['message']['content'] ?? ''; //@phpstan-ignore-line
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
        return $response->status() !== ResponseStatus::HTTP_OK || ! $response->json('choices');
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
