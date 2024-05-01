<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\AiServiceManagement\app\Http\Controllers\AiCallTypeController;
use Modules\AiServiceManagement\app\Http\Controllers\AiResponseTypeController;
use Modules\AiServiceManagement\app\Http\Controllers\AiServiceController;

Route::get('ai-call-types', [AiCallTypeController::class, 'index'])->name('ai-call-types.index');
Route::get('ai-response-types', [AiResponseTypeController::class, 'index'])->name('ai-response-types.index');
Route::get('ai-services', [AiServiceController::class, 'index'])->name('ai-services.index');
