<?php

declare(strict_types=1);

namespace Modules\Auth\app\Exceptions;

use Modules\Exceptions\app\Exceptions\BaseException;
use Symfony\Component\HttpFoundation\Response;

class EmailException extends BaseException
{
    public static function invalid(): self
    {
        return new self(
            message: __('Auth::exceptions.email.invalid_email'),
            code: Response::HTTP_INTERNAL_SERVER_ERROR,
            id: 'f8a2819b-7aa6-42ea-addd-00e70ef3b97c',
            name: static::getClassShortName() . ':invalid'
        );
    }
}
