<?php

declare(strict_types=1);

namespace Modules\Auth\app\Dtos;

use App\Dtos\BaseDto;
use Modules\Auth\app\Http\Requests\RefreshTokenRequest;
use Modules\Auth\app\Models\User;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class RefreshTokenDto extends BaseDto
{
    public function __construct(
        public User $authUser,
    ) {}

    public static function fromRefreshTokenRequest(RefreshTokenRequest $request): self
    {
        return new self(
            authUser: $request->user(),
        );
    }
}
