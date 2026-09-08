<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Modules\ProjectManagement\app\Http\Requests\ProjectGroup\ProjectGroupRequest;
use Modules\ProjectManagement\app\Http\Resources\ProjectGroupResource;
use Modules\ProjectManagement\app\Models\Project;
use Modules\ProjectManagement\app\Models\ProjectGroup;

final class ProjectGroupController
{
    public function index(): JsonResponse
    {
        /** @var \Modules\Auth\app\Models\User $user */
        $user = auth()->user();

        $groups = ProjectGroup::query()
            ->allowedForUser($user)
            ->withCount('projects')
            ->with(['projects' => static fn ($q) => $q->select(['id', 'key', 'name', 'project_group_id'])])
            ->latest()
            ->get();

        return apiResponse()
            ->success()
            ->data(ProjectGroupResource::collection($groups))
            ->send();
    }

    public function store(ProjectGroupRequest $request): JsonResponse
    {
        /** @var array<string, mixed> $data */
        $data = $request->validated();
        /** @var \Modules\Auth\app\Models\User $user */
        $user = auth()->user();

        /** @var ProjectGroup $group */
        $group = DB::transaction(static function () use ($data, $user): ProjectGroup {
            $group = ProjectGroup::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'instructions' => $data['instructions'] ?? null,
                'user_id' => $user->id,
            ]);

            $projectKeys = $data['project_keys'] ?? [];
            if ($projectKeys !== []) {
                Project::query()
                    ->whereIn('key', $projectKeys)
                    ->allowedForUser($user)
                    ->update(['project_group_id' => $group->id]);
            }

            return $group->load(['projects']);
        });

        return apiResponse()
            ->success()
            ->message('Project group created successfully')
            ->data(ProjectGroupResource::make($group))
            ->send();
    }

    public function show(string $projectGroup): JsonResponse
    {
        /** @var \Modules\Auth\app\Models\User $user */
        $user = auth()->user();

        /** @var ProjectGroup $group */
        $group = ProjectGroup::query()
            ->allowedForUser($user)
            ->where('key', $projectGroup)
            ->with(['projects' => static fn ($q) => $q->select(['id', 'key', 'name', 'project_group_id'])])
            ->firstOrFail();

        return apiResponse()
            ->success()
            ->data(ProjectGroupResource::make($group))
            ->send();
    }

    public function update(ProjectGroupRequest $request, string $projectGroup): JsonResponse
    {
        /** @var array<string, mixed> $data */
        $data = $request->validated();
        /** @var \Modules\Auth\app\Models\User $user */
        $user = auth()->user();

        /** @var ProjectGroup $group */
        $group = ProjectGroup::query()
            ->allowedForUser($user)
            ->where('key', $projectGroup)
            ->firstOrFail();

        DB::transaction(static function () use ($group, $data, $user): void {
            $group->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'instructions' => $data['instructions'] ?? null,
            ]);

            if (array_key_exists('project_keys', $data)) {
                $targetProjectKeys = $data['project_keys'] ?? [];
                $group->projects()->whereNotIn('key', $targetProjectKeys)->update(['project_group_id' => null]);

                if ($targetProjectKeys !== []) {
                    Project::query()
                        ->whereIn('key', $targetProjectKeys)
                        ->allowedForUser($user)
                        ->update(['project_group_id' => $group->id]);
                }
            }
        });

        $group->load(['projects' => static fn ($q) => $q->select(['id', 'key', 'name', 'project_group_id'])]);

        return apiResponse()
            ->success()
            ->message('Project group updated successfully')
            ->data(ProjectGroupResource::make($group))
            ->send();
    }

    public function destroy(string $projectGroup): JsonResponse
    {
        /** @var \Modules\Auth\app\Models\User $user */
        $user = auth()->user();

        /** @var ProjectGroup $group */
        $group = ProjectGroup::query()
            ->allowedForUser($user)
            ->where('key', $projectGroup)
            ->firstOrFail();

        DB::transaction(static function () use ($group): void {
            $group->projects()->update(['project_group_id' => null]);
            $group->delete();
        });

        return apiResponse()
            ->success()
            ->message('Project group deleted successfully')
            ->send();
    }
}
