<?php

declare(strict_types=1);

namespace MohamedGaber\ApiResponse;

use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\ServiceProvider;
use MohamedGaber\ApiResponse\Factory\ApiResponseFactory;

final class ApiResponseServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        app(ResponseFactory::class)->macro(
            'api',
            fn () => app(ApiResponseFactory::class)
        );
    }
}
