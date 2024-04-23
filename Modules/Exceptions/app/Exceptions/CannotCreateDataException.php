<?php

declare(strict_types=1);

namespace Modules\Exceptions\app\Exceptions;


use MohamedGaber\ApiResponse\Builder\ErrorApiResponseBuilder;
use Spatie\LaravelData\Exceptions\CannotCreateData;
use Throwable;

class CannotCreateDataException extends BaseException
{
    public function __construct(
        public CannotCreateData $baseCannotCreateDataException,
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null,
        $id = '',
        $name = ''
    ) {
        parent::__construct(
            $message ?? $this->baseCannotCreateDataException->getMessage(),
            $code,
            $previous,
            $id,
            $name
        );
    }

    protected function withResponse(ErrorApiResponseBuilder $response)
    {
        return $response->withMeta(
            config('app.debug') ? [
                'exception' => get_class($this->baseCannotCreateDataException),
                'line' => $this->baseCannotCreateDataException->getLine(),
                'file' => $this->baseCannotCreateDataException->getFile(),
                'trace' => $this->baseCannotCreateDataException->getTrace(),
            ] : []
        );
    }
}
