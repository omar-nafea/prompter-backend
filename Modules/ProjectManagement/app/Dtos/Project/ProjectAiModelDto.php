<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Dtos\Project;

use App\Dtos\BaseDto;
use Modules\AiServiceManagement\app\Enums\AiModelProvider;

final class ProjectAiModelDto extends BaseDto
{
    public function __construct(
        public string $name,
        public string $alias,
        public AiModelProvider $provider,
        public ?string $apiKey = null,
        public ?string $connectorUrl = null,
    ) {}
}
