<?php

declare(strict_types=1);

namespace MohamedGaber\ApiResponse\Factory;


use MohamedGaber\ApiResponse\Builder\ErrorApiResponseBuilder;
use MohamedGaber\ApiResponse\Builder\SuccessApiResponseBuilder;

class ApiResponseFactory
{
    public static function make()
    {
        return app(static::class);
    }

    public function success()
    {
        return app(SuccessApiResponseBuilder::class);
    }

    public function error()
    {
        return app(ErrorApiResponseBuilder::class);
    }
}
