<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\AiServiceManagement\app\Http\Controllers\AiCallTypeController;
use Modules\AiServiceManagement\app\Http\Controllers\AiModelController;
use Modules\AiServiceManagement\app\Http\Controllers\AiResponseTypeController;
use Modules\AiServiceManagement\app\Http\Controllers\AiServiceCallingController;

Route::get('ai-call-types', [AiCallTypeController::class, 'index'])->name('ai-call-types.index');
Route::get('ai-response-types', [AiResponseTypeController::class, 'index'])->name('ai-response-types.index');
Route::middleware('auth:sanctum')->group(static function (): void {
    Route::get('ai-model', [AiModelController::class, 'show'])->name('ai-model.show');
    Route::put('ai-model', [AiModelController::class, 'update'])->name('ai-model.update');
});

Route::post('call-ai-service', [AiServiceCallingController::class, 'ask'])->name('ai-service-calling.ask');
