<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation\Strategies;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation\Strategies\Contracts\AiCallTypeStrategy;
use Modules\ProjectManagement\app\Models\Project;
use Modules\ProjectManagement\app\Models\ProjectInput;

final class OneByOneCall implements AiCallTypeStrategy
{
    public function __construct(
        protected Project $project
    ) {}

    /**
     * @return array<string,string[]>
     */
    public function rules(): array
    {
        /** @var Collection<string, string[]> $rulesCollection */
        $rulesCollection = $this->project->inputs->mapWithKeys(
            callback: fn (ProjectInput $input): array => [
                $input->name => $this->getRoles($input),
            ]
        );

        /** @var array<string, string[]> */
        return $rulesCollection->toArray();
    }

    /**
     * @return string[]
     */
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
