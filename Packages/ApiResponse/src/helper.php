<?php

declare(strict_types=1);

use MohamedGaber\ApiResponse\Builder\ErrorApiResponseBuilder;
use MohamedGaber\ApiResponse\Builder\SuccessApiResponseBuilder;
use MohamedGaber\ApiResponse\Factory\ApiResponseFactory;

if ( ! function_exists('apiResponse')) {
    function apiResponse(): ApiResponseFactory
    {
        return app(ApiResponseFactory::class);
    }
}

if ( ! function_exists('apiSuccess')) {
    function apiSuccess(): SuccessApiResponseBuilder
    {
        return apiResponse()->success();
    }
}
if ( ! function_exists('apiError')) {
    function apiError(): ErrorApiResponseBuilder
    {
        return apiResponse()->error();
    }
}
