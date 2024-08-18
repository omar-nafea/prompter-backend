<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Requests\ProjectModerator;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Support\Facades\Gate;
use Modules\ProjectManagement\app\Models\Project;

final class FetchProjectModeratorListRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        //todo authorize to only project owner only
        //        Gate::authorize('',[x::class,$this->route('')]);
        $project = Project::query()
            ->allowedForUser($this->user())
            ->where('key', $this->route('project'))
            ->firstOrFail();
        if ($project->user_id !== auth()->id()) {
            apiError()
                ->message('You are not authorized to retrieve this project moderators')->send();
        }

        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
