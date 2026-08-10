<?php

declare(strict_types=1);

namespace Modules\UserManagement\app\Actions\User;

use Illuminate\Database\Eloquent\Collection;
use Modules\Auth\app\Models\User;

final class ListUsersAction
{
    /**
     * @return Collection<int, User>
     */
    public function execute(): Collection
    {
        return User::query()
            ->orderBy('id')
            ->get();
    }
}
