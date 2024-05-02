<?php

declare(strict_types=1);

use Modules\ProjectManagement\app\Http\Controllers\InputDataTypeController;
use Modules\ProjectManagement\app\Http\Controllers\ProjectController;
use Modules\ProjectManagement\app\Http\Controllers\ProjectObjectiveQuestionController;

Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');
Route::get('projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('projects/{project}', [ProjectController::class, 'show'])->name('projects.show');
Route::get('projects/{project}/code-snippets', [ProjectController::class, 'codeSnippets'])->name('projects.code-snippets');
Route::post('projects/validate/steps/{step}', [ProjectController::class, 'validateProjectFormOnly'])->name('projects.store.validate');
Route::get('project-objective-questions', [ProjectObjectiveQuestionController::class, 'index'])->name('project-objective-questions.index');
Route::get('input-data-types', [InputDataTypeController::class, 'index'])->name('input-data-types.index');
