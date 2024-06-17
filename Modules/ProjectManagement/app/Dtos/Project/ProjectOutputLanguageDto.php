<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Dtos\Project;

use App\Dtos\BaseDto;

final class ProjectOutputLanguageDto extends BaseDto
{
    public function __construct(
        public int $outputLanguageId
    ) {}
}
