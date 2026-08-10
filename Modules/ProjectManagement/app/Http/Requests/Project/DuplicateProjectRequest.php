<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Requests\Project;

use App\Http\Requests\BaseApiRequest;

final class DuplicateProjectRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-projects') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
