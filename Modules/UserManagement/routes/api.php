<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\UserManagement\app\Http\Controllers\ProfileController;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
});
