<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Requests\Project;

use App\Http\Requests\BaseApiRequest;
use App\Rules\ValidateInputOutputEnumValues;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\AiServiceManagement\app\Models\AiCallType;
use Modules\AiServiceManagement\app\Models\AiResponseType;
use Modules\AiServiceManagement\app\Models\AiService;
use Modules\ProjectManagement\app\Enums\DataType;
use Modules\ProjectManagement\app\Enums\OutputLanguageStatus;
use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;
use Modules\ProjectManagement\app\Models\OutputLanguage;
use Modules\ProjectManagement\app\Models\Project;
use Modules\ProjectManagement\app\Models\ProjectObjectiveQuestion;

class ProjectRequest extends BaseApiRequest
{
    protected bool $forEdit = false;

    protected int $currentStep = 4;

    protected ?Project $project = null;

    public function forEdit($forEdit = true): static
    {
        $this->forEdit = $forEdit;

        return $this;
    }

    public function forStep($step): static
    {
        $this->currentStep = $step;

        return $this;
    }

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->forEdit = $this->isMethod('PUT');
        if ($this->forEdit) {
            $this->project = Project::findOrFail($this->route('project'));
        }

        if ($step = (int) $this->route('step')) {
            $this->currentStep = $step;
        }

    }

    public function rules(): array
    {
        $rules = [];
        foreach (range(1, $this->currentStep) as $step) {
            $rules = [...$rules, ...$this->{"step{$step}rules"}()];
        }

        return $rules;
    }

    public function step1Rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                Rule::unique(Project::class, 'name')->where('user_id', auth()->id()),
                'min:' . config('global.min_string_length'),
                'max:' . config('global.max_string_length'),
            ],
            'objective_questions' => [
                'required',
                'array',
                'size:' . ProjectObjectiveQuestion::where('status', 1)->count(),
            ],
            'objective_questions.*' => [
                'required',
                'array',
                'size:2',
            ],
            'objective_questions.*.question_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(ProjectObjectiveQuestion::class, 'id')
                    ->where('status', 1)
                    ->withoutTrashed(),
            ],
            'objective_questions.*.answer' => [
                'required',
                'string',
                'min:' . config('global.min_text_length'),
                'max:' . config('global.max_text_length'),
            ],
            'expected_outcome' => [
                'required',
                'string',
                'min:' . config('global.min_text_length'),
                'max:' . config('global.max_text_length'),
            ],
            'max_output_length' => [
                'required',
                'int',
                'max:' . config('global.max_integer'),
            ],
            'output_format' => [
                'required',
                Rule::enum(ProjectOutputFormat::class),
            ],
            'output_languages' => [
                'required',
                'array',
                'filled',
                'distinct',
            ],
            'output_languages.*' => [
                'required',
                'filled',
                'distinct',
                Rule::exists(OutputLanguage::class, 'id')
                    ->where('status', OutputLanguageStatus::Enabled)
                    ->withoutTrashed(),
            ],

        ];
    }

    public function step2Rules(): array
    {
        return [
            'ai_service_id' => [
                'required',
                'integer',
                Rule::exists(AiService::class, 'id')
                    ->where('status', 1)//todo add ai service enum
                    ->withoutTrashed(),
            ],
            'ai_call_type_id' => [
                'required',
                'integer',
                Rule::exists(AiCallType::class, 'id')
                    ->where('status', 1)//todo add ai service enum
                    ->withoutTrashed(),
            ],
            'ai_response_type_id' => [
                'required',
                'integer',
                Rule::exists(AiResponseType::class, 'id')
                    ->where('status', 1)//todo add ai service enum
                    ->withoutTrashed(),
            ],
        ];
    }

    public function step3Rules(): array
    {
        return [
            'project_inputs' => [
                'required',
                'array',
            ],
            'project_inputs.*' => [
                'required',
                'array',
                new ValidateInputOutputEnumValues(),
            ],
            'project_inputs.*.name' => [
                'required',
                'string',
                'min:' . config('global.min_string_length'),
                'max:' . config('global.max_string_length'),
                'distinct',
            ],
            'project_inputs.*.data_type' => [
                'required',
                Rule::enum(DataType::class),
            ],
            'project_inputs.*.is_required' => [
                'required',
                'boolean',
            ],
            'project_inputs.*.max_length' => [
                'bail',
                'nullable',
                'int',
                'max:' . config('global.max_integer'),
            ],
            'project_inputs.*.description' => [
                'nullable',
                'string',
                'min:' . config('global.min_string_length'),
                'max:' . config('global.max_string_length'),
            ],
            'project_inputs.*.values' => [
                'sometimes',
            ],
        ];
    }

    public function step4Rules(): array
    {
        return [
            'project_outputs' => [
                'required',
                'array',
            ],
            'project_outputs.*' => [
                'required',
                'array',
                new ValidateInputOutputEnumValues(),
            ],
            'project_outputs.*.name' => [
                'required',
                'string',
                'min:' . config('global.min_string_length'),
                'max:' . config('global.max_string_length'),
                'distinct',
            ],
            'project_outputs.*.data_type' => [
                'required',
                Rule::enum(DataType::class),
            ],
            'project_outputs.*.is_required' => [
                'required',
                'boolean',
            ],
            'project_outputs.*.max_length' => [
                'bail',
                'nullable',
                'int',
                'max:' . config('global.max_integer'),
            ],
            'project_outputs.*.description' => [
                'nullable',
                'string',
                'min:' . config('global.min_string_length'),
                'max:' . config('global.max_string_length'),
            ],
            'project_outputs.*.values' => [
                'sometimes',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->count()) {
                $validator->errors()->add(
                    'failed_step',
                    $this->determineFailedRuleStep(Arr::first($validator->errors()->keys()))
                );
            }
        });
    }

    public function determineFailedRuleStep(string $failedRule): ?int
    {
        $result = null;
        foreach (range(1, 4) as $step) {
            $rules = array_keys($this->{"step{$step}rules"}());
            if (in_array($failedRule, $rules)) {
                $result = $step;
                break;
            }
        }

        return $result;
    }
}
