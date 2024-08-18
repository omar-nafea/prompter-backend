<?php

declare(strict_types=1);

use Modules\ProjectManagement\app\Http\Controllers\ProjectModeratorController;

Route::group(['prefix' => 'project-moderators', 'as' => 'project-moderators.'], static function (): void {
    Route::get('/', [ProjectModeratorController::class, 'index'])->name('index');
    Route::post('check-existence', [ProjectModeratorController::class, 'checkProjectModeratorExistence'])->name('check');
    Route::post('invite', [ProjectModeratorController::class, 'inviteModerator'])->name('invite');

    Route::group(['prefix' => '{moderator}'], static function (): void {

        Route::delete('/', [ProjectModeratorController::class, 'destroy'])->name('destroy');

    });
});
