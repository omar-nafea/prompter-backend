<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Stringable;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

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
        Stringable::macro('minify', function ($separator) {
            //            dd($separator);
            /** @var Stringable $this */
            return str(preg_replace(
                '/^\s*[\r\n]/m',
                '',
                str_replace("\n", $separator, $this->toString())
            ));
        });
        Str::macro(
            'minify',
            function ($value, $separator = '') {
                return preg_replace(
                    '/^\s*[\r\n]/m',
                    $separator,
                    str_replace("\n", $separator, $value ?? '')
                );
            }
        );
    }
}
//if ( ! function_exists('minify')) {
//    function minify($value, $separator = '')
//    {
//        return preg_replace(
//            '/^\s*[\r\n]/m',
//            '',
//            str_replace("\n", $separator, $value)
//        );
//    }
//}
