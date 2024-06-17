<?php

declare(strict_types=1);

namespace Modules\UserManagement\app\Http\Controllers;

use Illuminate\Auth\AuthManager;
use Illuminate\Http\Response;
use Modules\Auth\app\Http\Resources\UserResource;
use Modules\Auth\app\Models\User;
use Modules\UserManagement\app\Actions\Profile\FetchUserProfileAction;

final class ProfileController
{
    public function __construct(
        protected AuthManager $auth
    ) {}

    public function show(FetchUserProfileAction $action): Response
    {
        /** @var User $user */
        $user = $this->auth->user();
        return apiResponse()
            ->success()
            ->data(
                data: UserResource::make(
                    parameters: $action->execute(
                        user: $user
                    )
                )
            )->send();
    }
}
