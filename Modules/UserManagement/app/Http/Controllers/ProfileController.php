<?php

declare(strict_types=1);

namespace Modules\UserManagement\app\Http\Controllers;

use Modules\Auth\app\Http\Resources\UserResource;
use Modules\UserManagement\app\Actions\Profile\FetchUserProfileAction;

class ProfileController
{
    public function show(FetchUserProfileAction $action)
    {
        return apiResponse()
            ->success()
            ->data(UserResource::make(
                $action->execute(auth()->user())
            ))
            ->send();
    }
}
