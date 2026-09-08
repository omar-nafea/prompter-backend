<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Resources;

use App\Http\Resources\DateTimeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\ProjectManagement\app\Models\ProjectGroup;
use Override;

/**
 * @property-read ProjectGroup $resource
 */
final class ProjectGroupResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->key,
            'name' => $this->resource->name,
            'description' => $this->resource->description,
            'instructions' => $this->resource->instructions,
            'projects_count' => $this->resource->projects_count ?? $this->resource->projects()->count(),
            'projects' => $this->whenLoaded('projects', function () {
                return $this->resource->projects->map(fn ($project) => [
                    'id' => $project->key,
                    'name' => $project->name,
                ]);
            }),
            'created_at' => DateTimeResource::make($this->resource->created_at),
            'updated_at' => DateTimeResource::make($this->resource->updated_at),
        ];
    }
}
