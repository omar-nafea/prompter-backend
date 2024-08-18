<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\Auth\app\Http\Controllers\ControlPanel\ForgotPasswordController;
use Modules\Auth\app\Http\Controllers\ControlPanel\LoginController;
use Modules\Auth\app\Http\Controllers\ControlPanel\LogoutController;
use Modules\Auth\app\Http\Controllers\ControlPanel\RefreshTokenController;
use Modules\Auth\app\Http\Controllers\ControlPanel\RegisterController;
use Modules\Auth\app\Http\Controllers\ControlPanel\ResetPasswordController;
use Modules\Auth\app\Http\Controllers\ControlPanel\VerifyEmailController;

Route::post('login', LoginController::class)->name('auth.login')->middleware('throttle:5,1');
Route::post('register', RegisterController::class)->name('auth.register')->middleware('throttle:1,1');
Route::post('forgot-password', ForgotPasswordController::class)->name('forgot-password')->middleware('throttle:1,1');
Route::post('reset-password', ResetPasswordController::class)->name('reset-password')->middleware('throttle:1,1');
Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, 'verify'])
    ->name('verification.verify')->middleware('signed');

Route::group(['middleware' => 'auth:sanctum'], function (): void {
    Route::delete('logout', LogoutController::class)->name('auth.logout');
    Route::post('refresh', RefreshTokenController::class)->name('auth.refresh');
    Route::post('email/verification/resend', [VerifyEmailController::class, 'resend'])->middleware('throttle:1,1')
        ->name('verification.resend');
});
