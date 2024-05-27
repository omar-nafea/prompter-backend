<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask;

use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Actions\ConvertTextResponseToJsonAction;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Exceptions\FailedResponseException;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Plugins\HasTimeout;
use Throwable;

class AskRequest extends Request implements HasBody
{
    use HasJsonBody;
    use HasTimeout;

    protected int $connectTimeout = 60;

    protected int $requestTimeout = 120;

    protected Method $method = Method::POST;

    public function __construct(
        protected ConvertTextResponseToJsonAction $convertTextResponseToJsonAction
    ) {}

    public function resolveEndpoint(): string
    {
        return '/';
    }

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

    public function createDtoFromResponse(Response $response): mixed
    {
        $res['raw_response'] = $response->json();
        info(json_encode($response->json()));
        $res['data'] = $this->convertTextResponseToJsonAction->execute($response->json()['text'] ?? null);

        return AskResponseDto::fromResponse($res);
    }

    public function hasRequestFailed(Response $response): ?bool
    {
        return $response->status() !== \Symfony\Component\HttpFoundation\Response::HTTP_OK || ! $response->json('text');
        //        dd($response->status(),);
        //todo customize failed response
        //        return $response->failed();
    }

    public function getRequestException(Response $response, ?Throwable $senderException): ?Throwable
    {
        return FailedResponseException::failedAskResponse($response->json());
    }
}
