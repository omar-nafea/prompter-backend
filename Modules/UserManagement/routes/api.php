<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\UserManagement\app\Http\Controllers\ProfileController;
use Modules\UserManagement\app\Http\Controllers\UserController;

Route::middleware('auth:sanctum')->group(function (): void {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');

    Route::middleware('role:' . \Modules\Auth\app\Enums\UserRole::SuperAdmin->value)->group(function (): void {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{user}/password', [UserController::class, 'changePassword'])->name('users.password');
    });
});
