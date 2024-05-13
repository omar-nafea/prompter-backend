<?php

declare(strict_types=1);

namespace MohamedGaber\UniqueModelKeyGenerator;

use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;
use MohamedGaber\UniqueModelKeyGenerator\Contracts\UniqueModelKeyGeneratorFactory as UniqueModelKeyGeneratorFactoryContract;

class UniqueModelKeyGeneratorServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        if (! app()->configurationIsCached()) { // @phpstan-ignore-line
            $this->mergeConfigFrom(__DIR__ . '/../config/unique-model-key-generator.php', 'unique-model-key-generator');
        }

        $this->app->singleton(
            UniqueModelKeyGeneratorFactoryContract::class,
            UniqueModelKeyGeneratorFactory::class
        );

        Builder::macro('findOrFailByUniqueKey', function (string $key) {
            /** @var Builder $this */
            //todo implement
            throw new Exception('not implemented');
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/unique-model-key-generator.php' => config_path('unique-model-key-generator.php'),
        ], 'unique-model-key-generator-config');

    }
}
