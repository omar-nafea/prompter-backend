<?php

declare(strict_types=1);
Route::middleware('api')->group(
    function () {
        Route::get('api/test', function () {});
    }
);

Route::middleware('web')->group(
    function () {
        Route::get('test', function () {});
    }
);
