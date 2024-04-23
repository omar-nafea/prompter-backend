<?php
Route::middleware('api')->group(
function () {
 Route::get('api/test', function () {
     throw new \Illuminate\Database\Eloquent\ModelNotFoundException('asdasdas');
 });
 });

Route::middleware('web')->group(
    function () {
        Route::get('test', function () {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('asdasdas');
        });
    });
