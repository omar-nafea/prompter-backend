<?php

declare(strict_types=1);

namespace Modules\Auth\app\Http\Controllers\ControlPanel;

use Modules\Auth\app\Actions\ControlPanel\LoginAction;
use Modules\Auth\app\Dtos\ControlPanel\LoginDto;
use Modules\Auth\app\Http\Requests\LoginRequest;
use Modules\Auth\app\Http\Resources\TokenResource;
use Modules\Auth\app\Http\Resources\UserResource;

final class LoginController
{
    public function __invoke(LoginRequest $request, LoginAction $loginAction)
    {
        [
            'authToken' => $authToken,
            'refreshToken' => $refreshToken,
            'user' => $user
        ] = $loginAction->execute(
            LoginDto::fromLoginRequest($request)
        );

        return apiResponse()
            ->success()->message(__('auth::login.logged_in_successfully'))
            ->data([
                'auth_token' => TokenResource::make($authToken),
                'refresh_token' => TokenResource::make($refreshToken),
                'user' => UserResource::make($user),
            ])->send();

    }
}
