<?php

declare(strict_types=1);

namespace Modules\Auth\app\Actions;

use Modules\Auth\app\Dtos\RegisterDto;
use Modules\Auth\app\Models\User;

final class RegisterAction
{
    public function execute(RegisterDto $dto): array
    {
        $user = User::create($dto->toArray());

        return [
            'authToken' => $user->createAuthToken('ss-auth'),
            'refreshToken' => $user->createRefreshToken('ss-refresh'),
            'user' => $user,
        ];
    }
}
