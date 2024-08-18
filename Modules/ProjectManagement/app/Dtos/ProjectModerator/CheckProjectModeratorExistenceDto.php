<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Dtos\ProjectModerator;

use App\Dtos\BaseDto;
use App\ValueObjects\Email;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Http\Requests\ProjectModerator\CheckProjectModeratorExistenceRequest;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class CheckProjectModeratorExistenceDto extends BaseDto
{
    public function __construct(
        public string $projectId,
        public Email $email,
        public User $authUser,
    ) {}

    public static function fromCheckProjectModeratorExistenceRequest(CheckProjectModeratorExistenceRequest $request): self
    {
        return new self(
            projectId: (string) $request->route('project'),
            email: Email::from((string) $request->validated('email')),
            authUser: $request->user(),
        );
    }
}
