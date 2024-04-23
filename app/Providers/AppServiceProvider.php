<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }
        Model::preventLazyLoading($this->app->isLocal() && env('MODEL_SHOULD_BE_STRICT'));
        Model::preventSilentlyDiscardingAttributes($this->app->isLocal() && env('MODEL_SHOULD_BE_STRICT'));

        Str::macro('likeContains', fn ($value) => '%' . $value . '%');
        Str::macro('likeBeginWith', fn ($value) => $value . '%');
        Str::macro('likeEndWith', fn ($value) => '%' . $value);
    }
}
