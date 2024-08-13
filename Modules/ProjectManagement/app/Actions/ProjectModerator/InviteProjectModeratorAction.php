<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\ProjectModerator;

use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Dtos\ProjectModerator\InviteProjectModeratorDto;
use Modules\ProjectManagement\app\Models\Project;

final class InviteProjectModeratorAction
{
    public function execute(InviteProjectModeratorDto $dto): void
    {
        Project::query()
            ->allowedForUser($dto->authUser)
            ->where('key', $dto->projectId)
            ->firstOrFail()
            ->moderators()
            ->attach(
                $this->getUser($dto)
            );
        //todo send email
    }

    public function getUser(InviteProjectModeratorDto $dto): User
    {
        return User::query()
            ->allowedForUser($dto->authUser)
            ->where('email', $dto->email)
            ->firstOrCreate([
                'email' => $dto->email,
            ]);
    }
}
