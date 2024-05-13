<?php

declare(strict_types=1);

namespace MohamedGaber\UniqueModelKeyGenerator\Contracts;

interface UniqueModelKeyGeneratorFactory
{
    public function generate(string $prefix = ''): string;
}
