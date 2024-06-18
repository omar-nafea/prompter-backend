<?php

declare(strict_types=1);

namespace Modules\Exceptions\app\Exceptions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException as BaseModelNotFoundException;
use MohamedGaber\ApiResponse\Builder\ErrorApiResponseBuilder;
use Symfony\Component\HttpFoundation\Response as ResponseStatusCode;
use Throwable;

/**
 * @template TModel of Model
 */
final class ModelNotFoundException extends BaseException
{
    /**
     * @param  BaseModelNotFoundException<TModel>  $baseModelNotFoundException
     */
    public function __construct(
        public BaseModelNotFoundException $baseModelNotFoundException,
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null,
        mixed $id = '',
        mixed $name = ''
    ) {
        parent::__construct(
            $message ?? $this->baseModelNotFoundException->getMessage(),
            $code ?: ResponseStatusCode::HTTP_NOT_FOUND,
            $previous,
            $id,
            $name
        );
    }

    protected function withResponse(ErrorApiResponseBuilder $response): ErrorApiResponseBuilder
    {
        return $response->withMeta(
            config('app.debug') ? [
                'exception' => get_class($this->baseModelNotFoundException),
                'line' => $this->baseModelNotFoundException->getLine(),
                'file' => $this->baseModelNotFoundException->getFile(),
                'trace' => $this->baseModelNotFoundException->getTrace(),
            ] : []
        );
    }
}
