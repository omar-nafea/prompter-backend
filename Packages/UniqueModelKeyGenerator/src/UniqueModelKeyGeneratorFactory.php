<?php

declare(strict_types=1);

namespace MohamedGaber\UniqueModelKeyGenerator;

use Illuminate\Support\Str;
use MohamedGaber\UniqueModelKeyGenerator\Contracts\UniqueModelKeyGeneratorFactory as UniqueModelKeyGeneratorFactoryContract;

final class UniqueModelKeyGeneratorFactory implements UniqueModelKeyGeneratorFactoryContract
{
    public function generate(string $prefix = ''): string
    {
        return sprintf(
            '%s%s%s%s',
            config('unique-model-key-generator.prefix'),
            $prefix,
            $keyEntropy = Str::random(15),
            hash('crc32b', $keyEntropy)
        );
    }
}
