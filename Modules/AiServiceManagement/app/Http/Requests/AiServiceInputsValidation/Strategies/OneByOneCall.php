<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation\Strategies;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation\Strategies\Contracts\AiCallTypeStrategy;
use Modules\ProjectManagement\app\Enums\DataType;
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
                $input->name => $this->getRules($input),
            ]
        );
        //todo handle validate enum values
        //$rulesCollection->push(Rule::in($input->enumValues->pluck('name')));

        /** @var array<string, string[]> */
        return $rulesCollection->toArray();
    }

    /**
     * @return string[]
     */
    protected function getRules(ProjectInput $input): array
    {
        $rules = [];
        if ($input->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }
        if ($input->data_type !== DataType::Enum) {
            $rules[] = Str::lower($input->data_type->name);
        } else {
            $rules[] = 'array';
        }
        if ($input->max_length) {
            $rules[] = 'max:' . $input->max_length;
        }

        return $rules;
    }
}
