<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask;

use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Dtos\AskResponseDto;
use Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT3_0\Requests\Ask\Exceptions\FailedResponseException;
use Saloon\Contracts\Body\HasBody;
use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Traits\Body\HasJsonBody;
use Throwable;

class AskRequest extends Request implements HasBody
{
    use HasJsonBody;

    protected Method $method = Method::POST;

    public function resolveEndpoint(): string
    {
        return '/ask';
    }

    public function createDtoFromResponse(Response $response): mixed
    {
        return AskResponseDto::fromResponse($response->json());
    }

    public function hasRequestFailed(Response $response): ?bool
    {
        return $response->status() !== \Symfony\Component\HttpFoundation\Response::HTTP_OK;
        //        dd($response->status(),);
        //todo customize failed response
        //        return $response->failed();
    }

    public function getRequestException(Response $response, ?Throwable $senderException): ?Throwable
    {
        return new FailedResponseException('12312312');
    }
}
