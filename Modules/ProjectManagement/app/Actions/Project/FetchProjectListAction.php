<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\Project;

use Modules\Auth\app\Models\User;

class FetchProjectListAction
{
    public function execute(User $user)
    {
        return $user->projects()->with([
            'inputs',
            'outputs',
        ])->get();
    }
}
