<?php

declare(strict_types=1);

namespace App\Enums\MetaProperties;

use ArchTech\Enums\Meta\MetaProperty;
use Attribute;
use Override;

#[Attribute]
final class Enabled extends MetaProperty
{
    #[Override]
    protected function transform(mixed $value): bool
    {
        return (bool) $value;
    }
}
