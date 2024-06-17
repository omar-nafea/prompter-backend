<?php

declare(strict_types=1);

namespace Modules\UserManagement\app\Actions\Profile;

use Modules\Auth\app\Models\User;

final class FetchUserProfileAction
{
    public function execute(User $user)
    {
        return $user;
    }
}
