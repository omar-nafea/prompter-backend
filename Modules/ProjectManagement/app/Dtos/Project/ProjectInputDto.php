<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Dtos\Project;

use App\Dtos\BaseDto;
use Modules\ProjectManagement\app\Enums\DataType;

final class ProjectInputDto extends BaseDto
{
    public function __construct(
        public string $name,
        public DataType $dataType,
        public bool $isRequired,
        public ?int $maxLength = null,
        public ?string $description = null,
        /** @var array<int, string> */
        public ?array $values = null,
    ) {}
}
