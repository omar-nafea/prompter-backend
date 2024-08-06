<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Dtos;

use Spatie\LaravelData\Data;

final class AskPayloadDto extends Data
{
    public function __construct(
        public string $prompt
    ) {}
}
