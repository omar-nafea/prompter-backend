<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\App\Dtos\Project;

use App\Dtos\BaseDto;

class ProjectOutputLanguageDto extends BaseDto
{
    public function __construct(
        public int $outputLanguageId
    ) {}
}
