<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Dtos\Project;

use App\Dtos\BaseDto;

class ProjectDto extends BaseDto
{
    public function __construct(
        public string $name,
        public string $expectedOutcome,
        public int $aiServiceId,
        public int $aiCallTypeId,
        public int $aiResponseTypeId,
    ) {}
}
