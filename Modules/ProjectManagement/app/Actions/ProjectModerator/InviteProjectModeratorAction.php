<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Actions\ProjectModerator;

use App\Mail\ProjectModeratorInvitationMail;
use Mail;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Dtos\ProjectModerator\InviteProjectModeratorDto;
use Modules\ProjectManagement\app\Models\Project;

final class InviteProjectModeratorAction
{
    public function execute(InviteProjectModeratorDto $dto): void
    {
        $project = Project::query()
            ->allowedForUser($dto->authUser)
            ->where('key', $dto->projectId)
            ->firstOrFail();
        $project->moderators()
            ->attach(
                $user = $this->getUser($dto)
            );
        Mail::to(users: $user)
            ->send(mailable: new ProjectModeratorInvitationMail(project: $project));
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
