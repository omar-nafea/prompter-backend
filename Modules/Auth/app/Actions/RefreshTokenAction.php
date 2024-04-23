<?php

declare(strict_types=1);

namespace Modules\Auth\app\Actions;

use Modules\Auth\app\Dtos\RefreshTokenDto;

final class RefreshTokenAction
{
    public function execute(RefreshTokenDto $dto): mixed
    {
        $dto->authUser->currentAccessToken()->delete();

        return [
            'authToken' => $dto->authUser->createAuthToken('ss-auth'),
            'refreshToken' => $dto->authUser->createRefreshToken('ss-refresh'),
        ];
    }
}
