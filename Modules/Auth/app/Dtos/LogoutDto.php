<?php

declare(strict_types=1);

namespace Modules\Auth\app\Dtos;

use App\Dtos\BaseDto;
use Illuminate\Foundation\Auth\User;
use Modules\Auth\app\Http\Requests\LogoutRequest;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class LogoutDto extends BaseDto
{
    public function __construct(
        public User $authUser,
    ) {}

    public static function fromLogoutRequest(LogoutRequest $request): self
    {
        return new self(
            authUser: $request->user(),
        );
    }
}
