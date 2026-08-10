<?php

declare(strict_types=1);

namespace Modules\UserManagement\app\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Modules\Auth\app\Enums\UserRole;
use Modules\Auth\app\Http\Resources\UserResource;
use Modules\UserManagement\app\Actions\User\ChangeUserPasswordAction;
use Modules\UserManagement\app\Actions\User\CreateUserAction;
use Modules\UserManagement\app\Actions\User\ListUsersAction;
use Modules\UserManagement\app\Dtos\User\ChangeUserPasswordDto;
use Modules\UserManagement\app\Dtos\User\CreateUserDto;
use Modules\UserManagement\app\Http\Requests\User\ChangeUserPasswordRequest;
use Modules\UserManagement\app\Http\Requests\User\CreateUserRequest;

final class UserController
{
    public function index(ListUsersAction $action): JsonResponse
    {
        return apiResponse()
            ->success()
            ->data([
                'users' => UserResource::collection($action->execute()),
                'roles' => UserRole::selectOptions(),
            ])
            ->send();
    }

    public function store(CreateUserRequest $request, CreateUserAction $action): JsonResponse
    {
        $user = $action->execute(CreateUserDto::fromCreateUserRequest($request));

        return apiResponse()
            ->success()
            ->message('User created successfully')
            ->data(UserResource::make($user))
            ->send();
    }

    public function changePassword(
        ChangeUserPasswordRequest $request,
        ChangeUserPasswordAction $action,
    ): JsonResponse {
        $action->execute(ChangeUserPasswordDto::fromChangeUserPasswordRequest($request));

        return apiResponse()
            ->success()
            ->message('Password updated successfully')
            ->send();
    }
}
