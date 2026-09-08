<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\AiServiceManagement\app\Exceptions\ProjectException;
use Modules\ProjectManagement\app\Http\Resources\ProjectResource;
use Modules\ProjectManagement\app\Models\Project;
use Symfony\Component\HttpFoundation\Response;

final class ProjectMetadataController
{
    public function update(Request $request, string $project): JsonResponse
    {
        $resolvedProject = $this->authenticateAndResolveProject($request, $project);

        $validated = $request->validate([
            'metadata' => ['required', 'array'],
        ]);

        $resolvedProject->update([
            'metadata' => $validated['metadata'],
        ]);

        return apiResponse()
            ->success()
            ->message('Project metadata updated successfully')
            ->data([
                'project_id' => $resolvedProject->key,
                'metadata' => $resolvedProject->metadata,
                'project' => ProjectResource::make($resolvedProject),
            ])
            ->send();
    }

    private function authenticateAndResolveProject(Request $request, string $projectKey): Project
    {
        $apiKey = $request->header('X-Api-Key');
        $publicKey = $request->header('X-Public-Key');

        if (is_string($apiKey) && $apiKey !== '') {
            $keyToFind = is_string($publicKey) && $publicKey !== '' ? $publicKey : $projectKey;

            /** @var Project|null $project */
            $project = Project::query()->where('key', $keyToFind)->first();

            if ($project === null || ! hash_equals((string) $project->api_key, $apiKey)) {
                throw ProjectException::invalidPublicOrApiKey();
            }

            return $project;
        }

        /** @var \Modules\Auth\app\Models\User|null $user */
        $user = auth('sanctum')->user() ?? $request->user();

        if ($user !== null && $user->can('manage-projects')) {
            /** @var Project $project */
            $project = Project::query()
                ->allowedForUser($user)
                ->where('key', $projectKey)
                ->firstOrFail();

            return $project;
        }

        abort(Response::HTTP_UNAUTHORIZED, 'Authentication required via project API key or bearer token.');
    }
}
