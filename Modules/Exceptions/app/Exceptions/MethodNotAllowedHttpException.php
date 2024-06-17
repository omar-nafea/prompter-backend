<?php

declare(strict_types=1);

namespace Modules\Exceptions\app\Exceptions;

use MohamedGaber\ApiResponse\Builder\ErrorApiResponseBuilder;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException as BaseMethodNotAllowedHttpException;
use Throwable;

final class MethodNotAllowedHttpException extends BaseException
{
    public function __construct(
        public BaseMethodNotAllowedHttpException $baseMethodNotAllowedHttpException,
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null,
        $id = '',
        $name = ''
    ) {
        parent::__construct(
            $message ?? $this->baseMethodNotAllowedHttpException->getMessage(),
            $code ?: $this->baseMethodNotAllowedHttpException->getStatusCode(),
            $previous,
            $id,
            $name
        );
    }

    protected function withResponse(ErrorApiResponseBuilder $response)
    {
        return $response->withMeta(
            config('app.debug') ? [
                'exception' => get_class($this->baseMethodNotAllowedHttpException),
                'line' => $this->baseMethodNotAllowedHttpException->getLine(),
                'file' => $this->baseMethodNotAllowedHttpException->getFile(),
                'trace' => $this->baseMethodNotAllowedHttpException->getTrace(),
            ] : []
        );
    }
}
