<?php

declare(strict_types=1);

namespace Modules\Exceptions\app\Exceptions;

use Illuminate\Support\ItemNotFoundException as BaseItemNotFoundException;
use MohamedGaber\ApiResponse\Builder\ErrorApiResponseBuilder;
use Throwable;

final class ItemNotFoundException extends BaseException
{
    public function __construct(
        public BaseItemNotFoundException $baseItemNotFoundException,
        ?string $message = null,
        int $code = 0,
        ?Throwable $previous = null,
        $id = '',
        $name = ''
    ) {
        parent::__construct(
            $message ?? $this->baseItemNotFoundException->getMessage(),
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
                'exception' => get_class($this->baseItemNotFoundException),
                'line' => $this->baseItemNotFoundException->getLine(),
                'file' => $this->baseItemNotFoundException->getFile(),
                'trace' => $this->baseItemNotFoundException->getTrace(),
            ] : []
        );
    }
}
