<?php

declare(strict_types=1);

namespace App\Enums\MetaProperties;

use ArchTech\Enums\Meta\MetaProperty;
use Attribute;
use InvalidArgumentException;
use Override;

#[Attribute]
final class Example extends MetaProperty
{
    #[Override]
    protected function transform(mixed $value): string
    {
        if ( ! is_string($value)) {
            throw new InvalidArgumentException('value must be string but ' . gettype($value) . ' given');
        }

        return $value;
    }
}
