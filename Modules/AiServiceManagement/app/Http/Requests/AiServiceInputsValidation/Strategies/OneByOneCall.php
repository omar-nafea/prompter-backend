<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation\Strategies;

use Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation\Strategies\Contracts\AiCallTypeStrategy;
use Modules\ProjectManagement\app\Models\Project;
use Modules\ProjectManagement\app\Models\ProjectInput;
use Str;

class OneByOneCall implements AiCallTypeStrategy
{
    public function __construct(
        protected Project $project
    ) {}

    public function rules(): array
    {
        return $this->project->inputs->mapWithKeys(fn (ProjectInput $input) => [
            $input->name => $this->getRoles($input),
        ])->toArray();
    }

    protected function getRoles(ProjectInput $input): array
    {
        $rules = [];
        if ($input->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }
        $rules[] = Str::lower($input->data_type->name);
        if ($input->max_length) {
            $rules[] = 'max:' . $input->max_length;
        }

        return $rules;
    }
}
