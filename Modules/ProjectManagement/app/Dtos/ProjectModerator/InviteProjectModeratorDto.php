<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Dtos\ProjectModerator;

use App\Dtos\BaseDto;
use App\ValueObjects\Email;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Http\Requests\ProjectModerator\InviteProjectModeratorRequest;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class InviteProjectModeratorDto extends BaseDto
{
    public function __construct(
        public Email $email,
        public string $projectId,
        public User $authUser,
    ) {}

    public static function fromInviteProjectModeratorRequest(InviteProjectModeratorRequest $request): self
    {
        return new self(
            email: Email::from((string) $request->validated('email')),
            projectId: (string) $request->route('project'),
            authUser: $request->user(),
        );
    }
}
