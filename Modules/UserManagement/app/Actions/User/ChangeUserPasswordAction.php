<?php

declare(strict_types=1);

namespace Modules\UserManagement\app\Actions\User;

use Modules\Auth\app\Models\User;
use Modules\UserManagement\app\Dtos\User\ChangeUserPasswordDto;

final class ChangeUserPasswordAction
{
    public function execute(ChangeUserPasswordDto $dto): User
    {
        $dto->user->forceFill(['password' => $dto->password])->save();

        return $dto->user->refresh();
    }
}
