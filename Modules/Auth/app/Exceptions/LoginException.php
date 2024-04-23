<?php

declare(strict_types=1);

namespace Modules\Auth\app\Exceptions;

use Modules\Exceptions\app\Exceptions\BaseException;
use Symfony\Component\HttpFoundation\Response;

class LoginException extends BaseException
{
    public static function invalidCredentials(): self
    {
        return new self(
            message: __('auth::login.invalid_credentials'),
            code: Response::HTTP_NOT_FOUND,
            id: '41c7a10e-1a72-4e89-956a-ea91581ba226',
            name: static::getClassShortName() . ':invalidCredentials'
        );
    }
}
