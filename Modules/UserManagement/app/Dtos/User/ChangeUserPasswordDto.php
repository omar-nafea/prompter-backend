<?php

declare(strict_types=1);

namespace Modules\UserManagement\app\Dtos\User;

use App\Dtos\BaseDto;
use Modules\Auth\app\Models\User;
use Modules\UserManagement\app\Http\Requests\User\ChangeUserPasswordRequest;

final class ChangeUserPasswordDto extends BaseDto
{
    public function __construct(
        public User $user,
        public string $password,
    ) {}

    public static function fromChangeUserPasswordRequest(ChangeUserPasswordRequest $request): self
    {
        return self::from([
            'user' => $request->getTargetUser(),
            'password' => $request->validated('password'),
        ]);
    }
}
