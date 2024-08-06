<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Integerations\RapidApi\ChatGPT4_0\Requests\Ask\Exceptions;

use Modules\Exceptions\app\Exceptions\BaseException;
use Symfony\Component\HttpFoundation\Response;

final class FailedResponseException extends BaseException
{
    /**
     * @param  array<string, mixed>  $rawResponse
     */
    public static function failedAskResponse(array $rawResponse): self
    {
        return new self(
            message: __('ai-service-management::gateway.errors.failedAskResponse')
            . ' ' . json_encode($rawResponse),//todo handle debug mode here from config
            code: Response::HTTP_BAD_REQUEST,
            id: '84c0786c-2b8c-4229-97a9-36ce9c7de1b0',
            name: self::getClassShortName() . ':failedAskResponse'
        );
    }
}
