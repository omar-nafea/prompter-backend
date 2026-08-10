<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Requests\Project;

use App\Http\Requests\BaseApiRequest;
use Closure;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Models\Project;
use Override;

final class UpdateProjectLlmConfigurationRequest extends BaseApiRequest
{
    private Project $project;

    public function authorize(): bool
    {
        return $this->user()?->can('manage-projects') ?? false;
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        /** @var User $user */
        $user = $this->user();
        $this->project = Project::query()
            ->allowedForUser($user)
            ->where('key', $this->route('project'))
            ->firstOrFail();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'system_prompt' => ['nullable', 'string', 'max:20000'],
            'temperature' => ['required', 'numeric', 'between:0,2'],
            'max_output_tokens' => ['required', 'integer', 'min:1', 'max:32768'],
            'response_schema' => [
                'nullable',
                'array',
                static function (string $attribute, mixed $value, Closure $fail): void {
                    if (is_array($value) && ($value['type'] ?? null) !== 'object') {
                        $fail('The response schema must describe a top-level object.');
                    }
                },
            ],
        ];
    }

    public function project(): Project
    {
        return $this->project;
    }
}
