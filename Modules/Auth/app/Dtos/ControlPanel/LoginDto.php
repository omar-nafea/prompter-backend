<?php

declare(strict_types=1);

namespace Modules\Auth\app\Dtos\ControlPanel;

use App\Dtos\BaseDto;
use App\ValueObjects\Email;
use Modules\Auth\app\Http\Requests\LoginRequest;
use Spatie\LaravelData\Attributes\WithCastable;

final class LoginDto extends BaseDto
{
    public function __construct(
        #[WithCastable(Email::class)]
        public Email $email,
        public string $password,
    ) {}

    public static function fromLoginRequest(LoginRequest $request): self
    {
        return self::from(
            [
                'email' => $request->validated('email'),
                'password' => $request->validated('password'),
            ]
        );
    }
}
