<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Dtos\Project;

use App\Dtos\BaseDto;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Http\Requests\Project\DuplicateProjectRequest;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class DuplicateProjectDto extends BaseDto
{
    public function __construct(
        public string $projectKey,
        public User $authUser,
    ) {}

    public static function fromDuplicateProjectRequest(DuplicateProjectRequest $request): self
    {
        return new self(
            projectKey: (string) $request->route('project'),
            authUser: $request->user(),
        );
    }
}
