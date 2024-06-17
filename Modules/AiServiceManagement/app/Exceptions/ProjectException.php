<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Exceptions;

use Modules\Exceptions\app\Exceptions\BaseException;
use Symfony\Component\HttpFoundation\Response;

final class ProjectException extends BaseException
{
    public static function invalidPublicOrApiKey(): self
    {
        return new self(
            message: 'invalid public key or api key',
            code: Response::HTTP_FORBIDDEN,
            id: 'fbbafcf4-5434-4ec0-bdf9-81803ca3a6a0',
            name: static::getClassShortName() . ':invalidPublicOrApiKey'
        );
    }
}
