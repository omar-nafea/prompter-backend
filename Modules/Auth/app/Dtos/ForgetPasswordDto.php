<?php

declare(strict_types=1);

namespace Modules\Auth\app\Dtos;

use App\Dtos\BaseDto;
use App\ValueObjects\Email;
use Modules\Auth\app\Http\Requests\ForgetPasswordRequest;
use Spatie\LaravelData\Attributes\WithCastable;

final class ForgetPasswordDto extends BaseDto
{
    public function __construct(
        #[WithCastable(Email::class)]
        public Email $email,
    ) {}

    public static function fromForgetPasswordRequest(ForgetPasswordRequest $request): self
    {
        return self::from([
            'email' => $request->validated('email'),
        ]);
    }
}
