<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Dtos\ProjectModerator;

use App\Dtos\BaseDto;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Http\Requests\ProjectModerator\FetchProjectModeratorListRequest;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class FetchProjectModeratorListDto extends BaseDto
{
    public function __construct(
        public string $projectId,
        public User $authUser,
        public int $perPage,
        public int $page = 1,
    ) {}

    public static function fromFetchProjectModeratorListRequest(FetchProjectModeratorListRequest $request): self
    {
        return new self(
            projectId: (string) $request->route('project'),
            authUser: $request->user(),
            perPage: (int) $request->validated('per_page'),
            page: (int) $request->validated('page'),
        );
    }
}
