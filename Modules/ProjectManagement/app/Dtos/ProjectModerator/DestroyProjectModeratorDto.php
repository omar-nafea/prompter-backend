<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Dtos\ProjectModerator;

use App\Dtos\BaseDto;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Http\Requests\ProjectModerator\DestroyProjectModeratorRequest;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class DestroyProjectModeratorDto extends BaseDto
{
    public function __construct(
        public int $moderatorId,
        public string $projectId,
        public User $authUser,
    ) {}

    public static function fromDestroyProjectModeratorRequest(DestroyProjectModeratorRequest $request): self
    {
        return new self(
            moderatorId: (int) $request->route('moderator'),
            projectId: (string) $request->route('project'),
            authUser: $request->user(),
        );
    }
}
