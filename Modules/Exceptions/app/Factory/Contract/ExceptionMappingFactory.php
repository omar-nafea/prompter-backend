<?php

declare(strict_types=1);

namespace Modules\Exceptions\app\Factory\Contract;

use Throwable;

/**
 * @see \Modules\Exceptions\app\Factory\ExceptionMappingFactory
 * */
interface ExceptionMappingFactory
{
    public function handle(Throwable $exception): void;
}
