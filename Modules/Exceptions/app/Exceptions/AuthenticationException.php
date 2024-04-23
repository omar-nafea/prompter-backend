<?php

declare(strict_types=1);

namespace Modules\Exceptions\app\Exceptions;

use Illuminate\Auth\AuthenticationException as BaseAuthenticationException;
use MohamedGaber\ApiResponse\Builder\ErrorApiResponseBuilder;
use Symfony\Component\HttpFoundation\Response as ResponseStatusCode;
use Throwable;

class AuthenticationException extends BaseException
{
    public function __construct(
        public BaseAuthenticationException $baseAuthenticationException,
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null,
        $id = '',
        $name = ''
    ) {
        parent::__construct(
            __($message ?: $this->baseAuthenticationException->getMessage()),
            $code ?: ResponseStatusCode::HTTP_UNAUTHORIZED,
            $previous,
            $id,
            $name
        );
    }

    protected function withResponse(ErrorApiResponseBuilder $response)
    {
        return $response->withMeta([]);
    }
}
