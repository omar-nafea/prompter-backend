<?php

declare(strict_types=1);

namespace Modules\Auth\app\Http\Controllers\ControlPanel;

use Modules\Auth\app\Actions\LogoutAction;
use Modules\Auth\app\Dtos\LogoutDto;
use Modules\Auth\app\Http\Requests\LogoutRequest;

final class LogoutController
{
    public function __invoke(LogoutRequest $request, LogoutAction $logoutAction)
    {
        $logoutAction->execute(
            dto : LogoutDto::fromLogoutRequest($request)
        );

        return apiResponse()->success()->message(__('auth::logout.logged_out_successfully'))->send();
    }
}
