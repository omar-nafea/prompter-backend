<?php

declare(strict_types=1);

namespace Modules\Auth\app\Actions;

use Laravel\Sanctum\NewAccessToken;
use Modules\Auth\app\Dtos\RegisterDto;
use Modules\Auth\app\Models\User;

final class RegisterAction
{
    /**
     * @return array{ authToken: NewAccessToken, refreshToken: NewAccessToken, user: User }
     */
    public function execute(RegisterDto $dto): array
    {
        $user = User::create($dto->toArray());
        $user->sendEmailVerificationNotification();

        return [
            'authToken' => $user->createAuthToken('ss-auth'),
            'refreshToken' => $user->createRefreshToken('ss-refresh'),
            'user' => $user,
        ];
    }
}
