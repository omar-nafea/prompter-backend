<?php

declare(strict_types=1);

use Modules\ProjectManagement\app\Http\Controllers\InputDataTypeController;
use Modules\ProjectManagement\app\Http\Controllers\OutputLanguageController;
use Modules\ProjectManagement\app\Http\Controllers\ProjectController;
use Modules\ProjectManagement\app\Http\Controllers\ProjectObjectiveQuestionController;
use Modules\ProjectManagement\app\Http\Controllers\ProjectOutputFormatController;

Route::prefix('projects')->name('projects.')->as('projects.')->group(static function (): void {
    Route::post('/', [ProjectController::class, 'store'])->name('store');
    Route::get('/', [ProjectController::class, 'index'])->name('index');
    Route::get('{project}', [ProjectController::class, 'show'])->name('show');
    Route::put('{project}', [ProjectController::class, 'update'])->name('update');
    Route::delete('{project}', [ProjectController::class, 'destroy'])->name('destroy');
    Route::get('{project}/code-snippets', [ProjectController::class, 'codeSnippets'])->name('code-snippets');
    Route::post('validate/steps/{step}', [ProjectController::class, 'validateProjectFormOnly'])->name('store.validate');
    Route::put('validate/steps/{step}', [ProjectController::class, 'validateProjectFormOnly'])->name('update.validate');
});

Route::get('project-objective-questions', [ProjectObjectiveQuestionController::class, 'index'])->name('project-objective-questions.index');
Route::get('input-data-types', [InputDataTypeController::class, 'index'])->name('input-data-types.index');

Route::get('project-output-languages', OutputLanguageController::class)->name('project-output-languages.index');
Route::get('project-output-formats', ProjectOutputFormatController::class)->name('project-output-formats.name');
