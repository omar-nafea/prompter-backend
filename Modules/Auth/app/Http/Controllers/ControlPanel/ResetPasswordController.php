<?php

declare(strict_types=1);

namespace Modules\Auth\app\Http\Controllers\ControlPanel;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Password;
use Modules\Auth\app\Actions\ResetPasswordAction;
use Modules\Auth\app\Dtos\ResetPasswordDto;
use Modules\Auth\app\Http\Requests\ResetPasswordRequest;

final class ResetPasswordController
{
    public function __invoke(
        ResetPasswordRequest $request,
        ResetPasswordAction $action,
    ): Responsable {

        $action->execute(
            ResetPasswordDto::fromResetPasswordRequest($request)
        );

        return apiSuccess()->message(__(Password::PASSWORD_RESET));
    }
}
