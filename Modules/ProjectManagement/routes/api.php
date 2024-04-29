<?php

declare(strict_types=1);

use Modules\ProjectManagement\app\Http\Controllers\ProjectController;

Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
Route::post('projects/validate/steps/{step}', [ProjectController::class, 'validateProjectFormOnly'])->name('projects.store.validate');
