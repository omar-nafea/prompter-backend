<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Gateway\Dtos;

use Modules\ProjectManagement\app\Models\Project;
use Spatie\LaravelData\Data;

final class AskPayloadDto extends Data
{
    public function __construct(
        public string $prompt,
        public Project $project,
    ) {}
}
