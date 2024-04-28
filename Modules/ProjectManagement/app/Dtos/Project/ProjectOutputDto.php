<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Dtos\Project;

use App\Dtos\BaseDto;
use Modules\ProjectManagement\app\Enums\DataType;

final class ProjectOutputDto extends BaseDto
{
    public function __construct(
        public string $name,
        public DataType $dataType,
        public bool $isRequired,
        public int $maxLength,
        public string $description,
    ) {}
}
