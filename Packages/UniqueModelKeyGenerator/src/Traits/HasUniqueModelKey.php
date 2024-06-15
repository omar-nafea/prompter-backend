<?php

declare(strict_types=1);

namespace MohamedGaber\UniqueModelKeyGenerator\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use MohamedGaber\UniqueModelKeyGenerator\Contracts\UniqueModelKeyGeneratorFactory;

trait HasUniqueModelKey
{
    public static function bootHasUniqueModelKey(): void
    {
        static::creating(function (Model $model): void {
            $model->setAttribute($model::getUniqueKeyAttribute(), $model->generateUniqueKey());
        });
    }

    public function initializeHasUniqueModelKey(): void
    {
        $this->fillable[] = $this->getUniqueKeyAttribute();
    }

    protected static function getUniqueKeyAttribute(): string
    {
        return 'key';
    }

    protected function generateUniqueKey(string $prefix = ''): string
    {
        return app(UniqueModelKeyGeneratorFactory::class)->generate($prefix);
    }

    protected static function getUniqueKeyQuery(?string $key): Builder
    {
        return static::query()->where(
            static::getUniqueKeyAttribute(),
            $key
        );
    }

    public static function findByUniqueKey(?string $key): Model|self|null
    {
        return static::getUniqueKeyQuery($key)->first();
    }

    public static function findOrFailByUniqueKey(?string $key): Model|self|null
    {
        return static::getUniqueKeyQuery($key)->firstOrFail();
    }
}
