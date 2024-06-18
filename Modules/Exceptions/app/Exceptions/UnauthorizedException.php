<?php

declare(strict_types=1);

namespace Modules\Exceptions\app\Exceptions;

use Illuminate\Validation\UnauthorizedException as BaseUnauthorizedException;
use MohamedGaber\ApiResponse\Builder\ErrorApiResponseBuilder;
use Symfony\Component\HttpFoundation\Response as ResponseStatusCode;
use Throwable;

final class UnauthorizedException extends BaseException
{
    public function __construct(
        public BaseUnauthorizedException $baseUnauthorizedException,
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null,
        mixed $id = '',
        mixed $name = ''
    ) {
        parent::__construct(
            $message ?? $this->baseUnauthorizedException->getMessage(),
            $code ?: ResponseStatusCode::HTTP_FORBIDDEN,
            $previous,
            $id,
            $name
        );
    }

    protected function withResponse(ErrorApiResponseBuilder $response): ?ErrorApiResponseBuilder
    {
        return $response->withMeta(
            config('app.debug') ? [
                'exception' => get_class($this->baseUnauthorizedException),
                'line' => $this->baseUnauthorizedException->getLine(),
                'file' => $this->baseUnauthorizedException->getFile(),
                'trace' => $this->baseUnauthorizedException->getTrace(),
            ] : []
        );
    }
}
