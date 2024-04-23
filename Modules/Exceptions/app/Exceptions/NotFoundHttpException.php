<?php

declare(strict_types=1);

namespace Modules\Exceptions\app\Exceptions;

use MohamedGaber\ApiResponse\Builder\ErrorApiResponseBuilder;
use Symfony\Component\HttpFoundation\Response as ResponseStatusCode;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as BaseNotFoundHttpException;
use Throwable;

class NotFoundHttpException extends BaseException
{
    public function __construct(
        public BaseNotFoundHttpException $baseNotFoundHttpException,
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null,
        $id = '',
        $name = ''
    ) {
        parent::__construct(
            $message ?? $this->baseNotFoundHttpException->getMessage(),
            $code ?: ResponseStatusCode::HTTP_NOT_FOUND,
            $previous,
            $id,
            $name
        );
    }

    protected function withResponse(ErrorApiResponseBuilder $response)
    {
        return $response->withMeta(
            config('app.debug') ? [
                'exception' => get_class($this->baseNotFoundHttpException),
                'line' => $this->baseNotFoundHttpException->getLine(),
                'file' => $this->baseNotFoundHttpException->getFile(),
                'trace' => $this->baseNotFoundHttpException->getTrace(),
            ] : []
        );
    }
}
