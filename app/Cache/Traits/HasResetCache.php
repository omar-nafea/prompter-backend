<?php

declare(strict_types=1);

namespace App\Cache\Traits;

use App\Cache\Contracts\ShouldResetCache;
use Illuminate\Support\Facades\Cache;

trait HasResetCache
{
    protected static array $cacheTags = [];

    public static function bootHasResetCache(): void
    {
        static::$cacheTags[] = static::cacheTag();

        self::created(function (): void {
            static::resetCache();
        });

        self::updated(function (): void {
            static::resetCache();
        });

        self::deleted(function (): void {
            static::resetCache();
        });
    }

    public static function resetCache(): void
    {
        if ( ! (
            config('cache.models_cache_enabled')
            && isset(class_implements(static::class)[ShouldResetCache::class])
        )) {
            return;
        }

        if ( ! count(static::$cacheTags)) {
            static::$cacheTags[] = static::cacheTag();
        }

        Cache::tags(static::$cacheTags)->flush();
    }

    public static function cacheTag(): string
    {
        return md5(class_basename(static::class));
    }
}
