<?php

declare(strict_types=1);

namespace Modules\Auth\app\Actions;

use Laravel\Sanctum\NewAccessToken;
use Laravel\Sanctum\PersonalAccessToken;
use Modules\Auth\app\Dtos\RefreshTokenDto;
use RuntimeException;

final class RefreshTokenAction
{
    /**
     * @return array{ authToken: NewAccessToken, refreshToken: NewAccessToken }
     */
    public function execute(RefreshTokenDto $dto): array
    {
        $accessToken = $dto->authUser->currentAccessToken();
        if ( ! $accessToken instanceof PersonalAccessToken) {
            throw new RuntimeException('Current access token is not valid or cannot be deleted.');
        }
        $accessToken->delete();

        return [
            'authToken' => $dto->authUser->createAuthToken('ss-auth'),
            'refreshToken' => $dto->authUser->createRefreshToken('ss-refresh'),
        ];
    }
}
