<?php

declare(strict_types=1);

use Modules\ProjectManagement\app\CodeSnippets\Builder\CodeSnippetBuilder;
use Modules\ProjectManagement\app\Enums\ProgrammingLanguage;

Route::middleware('api')->group(
    function () {
        Route::get('api/test', function () {});
    }
);

Route::middleware('web')->group(
    function () {
        Route::get('test', function () {

            $project = \Modules\ProjectManagement\app\Models\Project::latest()->first();
            (new CodeSnippetBuilder())->language(ProgrammingLanguage::PHP)->forProject($project)->build();
        });
    }
);
