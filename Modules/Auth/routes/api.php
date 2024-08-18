<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Auth\app\Http\Controllers\ControlPanel\ForgotPasswordController;
use Modules\Auth\app\Http\Controllers\ControlPanel\LoginController;
use Modules\Auth\app\Http\Controllers\ControlPanel\LogoutController;
use Modules\Auth\app\Http\Controllers\ControlPanel\RefreshTokenController;
use Modules\Auth\app\Http\Controllers\ControlPanel\RegisterController;
use Modules\Auth\app\Http\Controllers\ControlPanel\ResetPasswordController;

Route::post('login', LoginController::class)->name('auth.login');
Route::post('register', RegisterController::class)->name('auth.register');
Route::post('forgot-password', ForgotPasswordController::class)->name('forgot-password');
Route::post('reset-password', ResetPasswordController::class)->name('reset-password');
Route::get('/email/verify/{id}/{hash}', Modules\Auth\app\Http\Controllers\ControlPanel\VerifyEmailController::class)
    ->name('verification.verify')->middleware('signed');
Route::group(['middleware' => 'auth:sanctum'], function (): void {
    Route::delete('logout', LogoutController::class)->name('auth.logout');
    Route::post('refresh', RefreshTokenController::class)->name('auth.refresh');
});
