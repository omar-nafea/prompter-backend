<?php

declare(strict_types=1);

namespace App\Enums\MetaProperties;

use ArchTech\Enums\Meta\MetaProperty;
use Attribute;

#[Attribute]
class Label extends MetaProperty
{
    protected function transform(mixed $value): mixed
    {
        return trans($value);
    }
}
