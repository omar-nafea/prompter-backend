<?php

declare(strict_types=1);

namespace Modules\Exceptions\app\Exceptions;

use Illuminate\Auth\Access\AuthorizationException as BaseAuthorizationException;
use MohamedGaber\ApiResponse\Builder\ErrorApiResponseBuilder;
use Symfony\Component\HttpFoundation\Response as ResponseStatusCode;
use Throwable;

final class AuthorizationException extends BaseException
{
    public function __construct(
        public BaseAuthorizationException $baseAuthorizationException,
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null,
        $id = '',
        $name = ''
    ) {
        parent::__construct(
            $message ?: $this->baseAuthorizationException->getMessage(),
            $code ?: ResponseStatusCode::HTTP_FORBIDDEN,
            $previous ?: $this->baseAuthorizationException->getPrevious(),
            $id ?: $code ?: $this->baseAuthorizationException->getCode(),
            $name ?: self::getClassShortName()
        );
    }

    //    protected function withResponse(ErrorApiResponseBuilder $response)
    //    {
    //        return $response->withMeta([]);
    //    }
}
