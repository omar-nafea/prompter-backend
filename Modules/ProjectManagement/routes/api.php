<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Modules\ProjectManagement\app\Http\Controllers\InputDataTypeController;
use Modules\ProjectManagement\app\Http\Controllers\OutputLanguageController;
use Modules\ProjectManagement\app\Http\Controllers\ProjectController;
use Modules\ProjectManagement\app\Http\Controllers\ProjectGroupController;
use Modules\ProjectManagement\app\Http\Controllers\ProjectMetadataController;
use Modules\ProjectManagement\app\Http\Controllers\ProjectObjectiveQuestionController;
use Modules\ProjectManagement\app\Http\Controllers\ProjectOutputFormatController;
use Modules\ProjectManagement\app\Http\Controllers\PrompterTestController;

Route::match(['post', 'put'], 'projects/{project}/metadata', [ProjectMetadataController::class, 'update'])
    ->name('projects.metadata.update');

Route::middleware(['auth:sanctum', 'verified'])->group(static function (): void {
    Route::get('prompter-test/samples', [PrompterTestController::class, 'samples'])->name('prompter-test.samples');

    Route::prefix('projects')->name('projects.')->as('projects.')->group(static function (): void {
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('/', [ProjectController::class, 'index'])->name('index');
        Route::get('{project}', [ProjectController::class, 'show'])->name('show');
        Route::put('{project}', [ProjectController::class, 'update'])->name('update');
        Route::patch('{project}', [ProjectController::class, 'patch'])->name('patch');
        Route::put('{project}/llm-configuration', [ProjectController::class, 'updateLlmConfiguration'])
            ->name('llm-configuration.update');
        Route::delete('{project}', [ProjectController::class, 'destroy'])->name('destroy');
        Route::post('{project}/duplicate', [ProjectController::class, 'duplicate'])->name('duplicate');
        Route::get('{project}/code-snippets', [ProjectController::class, 'codeSnippets'])->name('code-snippets');
        Route::post('{project}/test-inputs', [ProjectController::class, 'generateTestInputs'])->name('test-inputs');
        Route::post('validate/steps/{step}', [ProjectController::class, 'validateProjectFormOnly'])->name('store.validate');
        Route::put('{project}/validate/steps/{step}', [ProjectController::class, 'validateProjectFormOnly'])->name('update.validate');
        Route::prefix('{project}')->group(static function (): void {
            require_once __DIR__ . '/api/project-moderators.php';
        });
    });

    Route::prefix('project-groups')->name('project-groups.')->as('project-groups.')->group(static function (): void {
        Route::get('/', [ProjectGroupController::class, 'index'])->name('index');
        Route::post('/', [ProjectGroupController::class, 'store'])->name('store');
        Route::get('{projectGroup}', [ProjectGroupController::class, 'show'])->name('show');
        Route::put('{projectGroup}', [ProjectGroupController::class, 'update'])->name('update');
        Route::delete('{projectGroup}', [ProjectGroupController::class, 'destroy'])->name('destroy');
    });

    Route::get('project-objective-questions', [ProjectObjectiveQuestionController::class, 'index'])->name('project-objective-questions.index');
    Route::get('input-data-types', [InputDataTypeController::class, 'index'])->name('input-data-types.index');
    Route::get('project-output-languages', OutputLanguageController::class)->name('project-output-languages.index');
    Route::get('project-output-formats', ProjectOutputFormatController::class)->name('project-output-formats.name');
});
