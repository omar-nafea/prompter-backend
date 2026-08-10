<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Requests\Project;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Enums\DataType;
use Modules\ProjectManagement\app\Enums\OutputLanguageStatus;
use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;
use Modules\ProjectManagement\app\Models\OutputLanguage;
use Modules\ProjectManagement\app\Models\Project;
use Modules\ProjectManagement\app\Models\ProjectInput;
use Modules\ProjectManagement\app\Models\ProjectObjectiveAnswer;
use Modules\ProjectManagement\app\Models\ProjectOutput;
use Override;

final class PatchProjectRequest extends BaseApiRequest
{
    private const PATCHABLE_KEYS = [
        'name',
        'expected_outcome',
        'max_output_length',
        'output_format',
        'output_languages',
        'objective_questions',
        'project_inputs',
        'project_outputs',
    ];

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
            'name' => [
                'sometimes',
                'string',
                Rule::unique(Project::class, 'name')
                    ->where('user_id', $this->project->user_id)
                    ->ignore($this->project->id),
                'min:' . config('global.min_string_length'),
                'max:' . config('global.max_string_length'),
            ],
            'expected_outcome' => [
                'sometimes',
                'string',
                'min:' . config('global.min_text_length'),
                'max:' . config('global.max_text_length'),
            ],
            'max_output_length' => ['sometimes', 'integer', 'max:' . config('global.max_integer')],
            'output_format' => ['sometimes', Rule::enum(ProjectOutputFormat::class)],
            'output_languages' => ['sometimes', 'array', 'filled', 'distinct'],
            'output_languages.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(OutputLanguage::class, 'id')
                    ->where('status', OutputLanguageStatus::Enabled)
                    ->whereNull('deleted_at'),
            ],
            'objective_questions' => ['sometimes', 'array', 'min:1'],
            'objective_questions.*' => ['required', 'array'],
            'objective_questions.*.answer_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(ProjectObjectiveAnswer::class, 'id')
                    ->where('project_id', $this->project->id)
                    ->whereNull('deleted_at'),
            ],
            'objective_questions.*.answer' => [
                'required',
                'string',
                'min:' . config('global.min_text_length'),
                'max:' . config('global.max_text_length'),
            ],
            'project_inputs' => ['sometimes', 'array', 'min:1'],
            'project_inputs.*' => ['required', 'array'],
            'project_inputs.*.id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(ProjectInput::class, 'id')
                    ->where('project_id', $this->project->id)
                    ->whereNull('deleted_at'),
            ],
            'project_inputs.*.name' => [
                'sometimes',
                'string',
                'min:' . config('global.min_string_length'),
                'max:' . config('global.max_string_length'),
                'distinct',
            ],
            'project_inputs.*.data_type' => ['sometimes', Rule::enum(DataType::class)],
            'project_inputs.*.is_required' => ['sometimes', 'boolean'],
            'project_inputs.*.max_length' => ['sometimes', 'nullable', 'integer', 'max:' . config('global.max_integer')],
            'project_inputs.*.description' => [
                'sometimes',
                'nullable',
                'string',
                'min:' . config('global.min_string_length'),
                'max:' . config('global.max_text_length'),
            ],
            'project_inputs.*.values' => ['sometimes', 'array'],
            'project_inputs.*.values.*' => [
                'required',
                'string',
                'distinct',
                'max:' . config('global.max_string_length'),
            ],
            'project_outputs' => ['sometimes', 'array', 'min:1'],
            'project_outputs.*' => ['required', 'array'],
            'project_outputs.*.id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(ProjectOutput::class, 'id')
                    ->where('project_id', $this->project->id)
                    ->whereNull('deleted_at'),
            ],
            'project_outputs.*.name' => [
                'sometimes',
                'string',
                'min:' . config('global.min_string_length'),
                'max:' . config('global.max_string_length'),
                'distinct',
            ],
            'project_outputs.*.data_type' => ['sometimes', Rule::enum(DataType::class)],
            'project_outputs.*.is_required' => ['sometimes', 'boolean'],
            'project_outputs.*.max_length' => ['sometimes', 'nullable', 'integer', 'max:' . config('global.max_integer')],
            'project_outputs.*.description' => [
                'sometimes',
                'nullable',
                'string',
                'min:' . config('global.min_string_length'),
                'max:' . config('global.max_text_length'),
            ],
            'project_outputs.*.values' => ['sometimes', 'array'],
            'project_outputs.*.values.*' => [
                'required',
                'string',
                'distinct',
                'max:' . config('global.max_string_length'),
            ],
        ];
    }

    public function project(): Project
    {
        return $this->project;
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            if (array_intersect_key($validator->validated(), array_flip(self::PATCHABLE_KEYS)) === []) {
                $validator->errors()->add('project', 'At least one project field must be provided.');

                return;
            }

            $this->validateEnumValues($validator, 'project_inputs', 'inputs');
            $this->validateEnumValues($validator, 'project_outputs', 'outputs');
        });
    }

    private function validateEnumValues(Validator $validator, string $payloadKey, string $relation): void
    {
        /** @var array<int, array<string, mixed>> $fields */
        $fields = $this->input($payloadKey, []);

        foreach ($fields as $index => $fieldData) {
            $field = $this->project->{$relation}()->findOrFail($fieldData['id']);
            /** @var int|string|null $patchedDataType */
            $patchedDataType = $fieldData['data_type'] ?? null;
            $dataType = $patchedDataType !== null
                ? DataType::from((int) $patchedDataType)
                : $field->data_type;

            if (array_key_exists('values', $fieldData) && $dataType !== DataType::Enum) {
                $validator->errors()->add("{$payloadKey}.{$index}.values", 'Enum values are only valid for categorical fields.');
            }

            if (
                isset($fieldData['data_type'])
                && $dataType === DataType::Enum
                && $field->data_type !== DataType::Enum
                && ! array_key_exists('values', $fieldData)
            ) {
                $validator->errors()->add("{$payloadKey}.{$index}.values", 'Enum values are required when changing a field to categorical.');
            }
        }
    }
}
