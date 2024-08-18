<?php

declare(strict_types=1);

namespace Modules\Auth\app\Exceptions;

use Modules\Exceptions\app\Exceptions\BaseException;
use Symfony\Component\HttpFoundation\Response;

final class ForgetPasswordException extends BaseException
{
    public static function failedSendingPasswordResetLink(string $status): self
    {
        return new self(
            message: __($status),
            code: Response::HTTP_BAD_REQUEST,
            id: '8c287009-ab0d-4aeb-90a0-4c2215d6d3f0',
            name: self::getClassShortName() . ':failedSendingPasswordResetLink'
        );
    }

    public static function throttledSendingPasswordResetLink(string $status): self
    {
        return new self(
            message: __($status),
            code: Response::HTTP_TOO_MANY_REQUESTS,
            id: '5b0b26bb-0b9d-4197-95f2-8e556c07f162',
            name: self::getClassShortName() . ':throttledSendingPasswordResetLink'
        );
    }
}
