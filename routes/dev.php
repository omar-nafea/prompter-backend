<?php

declare(strict_types=1);

use MohamedGaber\UniqueModelKeyGenerator\Contracts\UniqueModelKeyGeneratorFactory;

Route::middleware('api')->group(
    function () {
        Route::get('api/test', function () {});
    }
);

Route::middleware('web')->group(
    function () {
        Route::get('test', function () {
            \Modules\ProjectManagement\app\Models\Project::query()->get()->each(function ($project) {
                $project->update([
                    'key' => app(UniqueModelKeyGeneratorFactory::class)->generate(),
                ]);
            });
        });
    }
);
