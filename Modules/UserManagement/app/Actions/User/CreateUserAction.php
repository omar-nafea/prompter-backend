<?php

declare(strict_types=1);

namespace Modules\UserManagement\app\Actions\User;

use Modules\Auth\app\Enums\UserStatus;
use Modules\Auth\app\Models\User;
use Modules\UserManagement\app\Dtos\User\CreateUserDto;

final class CreateUserAction
{
    public function execute(CreateUserDto $dto): User
    {
        return User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->password,
            'phone' => $dto->phone,
            'role' => $dto->role,
            'status' => UserStatus::Active,
            'email_verified_at' => now(),
        ]);
    }
}
