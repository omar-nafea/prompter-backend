<?php

declare(strict_types=1);

namespace App\Cache\Contracts;

interface ShouldResetCache
{
    public static function resetCache(): void;

    public static function cacheTag(): string;
}
