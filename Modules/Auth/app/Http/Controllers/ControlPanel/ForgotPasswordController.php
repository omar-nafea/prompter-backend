<?php

declare(strict_types=1);

namespace Modules\Auth\app\Http\Controllers\ControlPanel;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Facades\Password;
use Modules\Auth\app\Actions\ForgetPasswordAction;
use Modules\Auth\app\Dtos\ForgetPasswordDto;
use Modules\Auth\app\Http\Requests\ForgetPasswordRequest;

final class ForgotPasswordController
{
    public function __invoke(
        ForgetPasswordRequest $request,
        ForgetPasswordAction $action
    ): Responsable {
        $action->execute(
            ForgetPasswordDto::fromForgetPasswordRequest($request)
        );

        return apiSuccess()->message(__(Password::RESET_LINK_SENT));
    }
}
