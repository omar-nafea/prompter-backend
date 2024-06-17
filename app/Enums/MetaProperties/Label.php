<?php

declare(strict_types=1);

namespace App\Enums\MetaProperties;

use ArchTech\Enums\Meta\MetaProperty;
use Attribute;
use InvalidArgumentException;
use Override;

#[Attribute]
final class Label extends MetaProperty
{
    #[Override]
    protected function transform(mixed $value): mixed
    {
        if ( ! is_string($value)) {
            throw new InvalidArgumentException('value must be string but ' . gettype($value) . ' given');
        }

        return trans($value);
    }
}
