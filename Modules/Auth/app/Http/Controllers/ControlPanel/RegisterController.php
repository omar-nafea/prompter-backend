<?php

declare(strict_types=1);

namespace Modules\Auth\app\Http\Controllers\ControlPanel;

use Modules\Auth\app\Actions\RegisterAction;
use Modules\Auth\app\Dtos\RegisterDto;
use Modules\Auth\app\Http\Requests\RegisterRequest;
use Modules\Auth\app\Http\Resources\TokenResource;
use Modules\Auth\app\Http\Resources\UserResource;

final class RegisterController
{
    public function __invoke(RegisterRequest $request, RegisterAction $action)
    {
        [
            'authToken' => $authToken,
            'refreshToken' => $refreshToken,
            'user' => $user
        ] = $action->execute(
            dto: RegisterDto::fromRegisterRequest($request),
        );

        return apiResponse()
            ->success()->message(__('auth::register.registered_successfully'))
            ->data([
                'auth_token' => TokenResource::make($authToken),
                'refresh_token' => TokenResource::make($refreshToken),
                'user' => UserResource::make($user),
            ])->send();
    }
}
