<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Requests\ProjectGroup;

use App\Http\Requests\BaseApiRequest;

final class ProjectGroupRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-projects') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'instructions' => ['nullable', 'string'],
            'project_keys' => ['nullable', 'array'],
            'project_keys.*' => ['required', 'string'],
        ];
    }
}
