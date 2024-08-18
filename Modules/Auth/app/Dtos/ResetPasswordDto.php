<?php

declare(strict_types=1);

namespace Modules\Auth\app\Dtos;

use App\Dtos\BaseDto;
use App\ValueObjects\Email;
use Modules\Auth\app\Http\Requests\ResetPasswordRequest;
use Spatie\LaravelData\Attributes\MapInputName;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\Attributes\WithCastable;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;

#[MapInputName(SnakeCaseMapper::class)]
#[MapOutputName(SnakeCaseMapper::class)]
final class ResetPasswordDto extends BaseDto
{
    public function __construct(
        #[WithCastable(Email::class)]
        public Email $email,
        public string $token,
        public string $password,
        public string $passwordConfirmation,
    ) {}

    public static function fromResetPasswordRequest(ResetPasswordRequest $request): self
    {
        return self::from([
            'email' => $request->validated('email'),
            'token' => $request->validated('token'),
            'password' => $request->validated('password'),
            'passwordConfirmation' => $request->input('password_confirmation'),
        ]);
    }
}
