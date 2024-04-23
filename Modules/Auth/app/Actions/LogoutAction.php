<?php

declare(strict_types=1);

namespace Modules\Auth\app\Actions;

use Modules\Auth\app\Dtos\LogoutDto;

final class LogoutAction
{
    public function execute(LogoutDto $dto): bool
    {
        return $dto->authUser->currentAccessToken()->delete();
        //todo delete related refresh token

        // $user->tokens()->where('session_id', $user->currentAccessToken()->session_id)->delete();
    }
}
