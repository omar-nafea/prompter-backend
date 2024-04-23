<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Auth\app\Http\Controllers\ControlPanel\LoginController;
use Modules\Auth\app\Http\Controllers\ControlPanel\LogoutController;
use Modules\Auth\app\Http\Controllers\ControlPanel\RefreshTokenController;
use Modules\Auth\app\Http\Controllers\ControlPanel\RegisterController;

Route::post('login', LoginController::class)->name('auth.login');
Route::post('register', RegisterController::class)->name('auth.register');
Route::group(['middleware' => 'auth:sanctum'], function () {
    Route::delete('logout', LogoutController::class)->name('auth.logout');
    Route::post('refresh', RefreshTokenController::class)->name('auth.refresh');
});
