<?php

declare(strict_types=1);

namespace Modules\Auth\app\Http\Controllers\ControlPanel;

use Modules\Auth\app\Actions\RefreshTokenAction;
use Modules\Auth\app\Dtos\RefreshTokenDto;
use Modules\Auth\app\Http\Requests\RefreshTokenRequest;
use Modules\Auth\app\Http\Resources\TokenResource;

final class RefreshTokenController
{
    public function __invoke(RefreshTokenRequest $request, RefreshTokenAction $refreshTokenAction)
    {
        [
            'authToken' => $authToken,
            'refreshToken' => $refreshToken
        ] = $refreshTokenAction->execute(
            dto : RefreshTokenDto::fromRefreshTokenRequest($request)
        );

        return apiResponse()
            ->success()->message(__('auth::refresh-token.tokens_refreshed'))
            ->data([
                'auth_token' => TokenResource::make($authToken),
                'refresh_token' => TokenResource::make($refreshToken),
            ])->send();
    }
}
