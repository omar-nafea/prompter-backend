<?php

declare(strict_types=1);

namespace Modules\Auth\app\Actions;

use Laravel\Sanctum\PersonalAccessToken;
use Modules\Auth\app\Dtos\LogoutDto;
use RuntimeException;

final class LogoutAction
{
    public function execute(LogoutDto $dto): ?bool
    {
        $accessToken = $dto->authUser->currentAccessToken();
        if ( ! $accessToken instanceof PersonalAccessToken) {
            throw new RuntimeException('Current access token is not valid or cannot be deleted.');
        }

        return $accessToken->delete();
        //todo delete related refresh token

        // $user->tokens()->where('session_id', $user->currentAccessToken()->session_id)->delete();
    }
}
