<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Requests\ProjectModerator;

use App\Http\Requests\BaseApiRequest;
use App\x;
use Illuminate\Support\Facades\Gate;
use Modules\ProjectManagement\app\Models\Project;

final class DestroyProjectModeratorRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        //        Gate::authorize('',[x::class,$this->route('')]);
        $project = Project::query()
            ->allowedForUser($this->user())
            ->where('key', $this->route('project'))
            ->firstOrFail();
        if ($project->user_id !== auth()->id()) {
            apiError()
                ->message('You are not authorized to delete a project moderator')->send();
        }

        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
