<?php

declare(strict_types=1);


use MohamedGaber\ApiResponse\Factory\ApiResponseFactory;

if (!function_exists('apiResponse')){
    function apiResponse() : ApiResponseFactory
    {
        return app(ApiResponseFactory::class);
    }
}

