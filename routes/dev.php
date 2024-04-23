<?php
Route::middleware('api')->group(
function () {
 Route::get('api/test', function () {
     return apiResponse()->success()->send();
 });
 });

Route::middleware('web')->group(
    function () {
        Route::get('test', function () {
            return apiResponse()->success()->send();
        });
    });
