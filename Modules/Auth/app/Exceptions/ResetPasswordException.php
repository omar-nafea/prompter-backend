<?php

declare(strict_types=1);

namespace Modules\Auth\app\Exceptions;

use Modules\Exceptions\app\Exceptions\BaseException;
use Symfony\Component\HttpFoundation\Response;

final class ResetPasswordException extends BaseException
{
    public static function failedResetPassword(string $status): self
    {
        return new self(
            message: __($status),
            code: Response::HTTP_BAD_REQUEST,
            id: 'b73ab121-dbc2-49d5-bae4-811d145b84b7',
            name: self::getClassShortName() . ':failedResetPassword'
        );
    }
}
