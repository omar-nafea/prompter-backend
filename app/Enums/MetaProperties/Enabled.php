<?php

declare(strict_types=1);

namespace App\Enums\MetaProperties;

use ArchTech\Enums\Meta\MetaProperty;
use Attribute;

#[Attribute]
class Enabled extends MetaProperty
{
    protected function transform(mixed $value): bool
    {
        return (bool) $value;
    }
}
