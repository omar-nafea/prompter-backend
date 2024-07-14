<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::get(
    '/',
    fn () => response()->api()->success()
        ->data(['name' => config('app.name')])
        ->message(__('it works'))
);
