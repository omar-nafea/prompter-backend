<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Requests\Project;

use App\Http\Requests\BaseApiRequest;
use App\Rules\ValidateInputOutputEnumValues;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\AiServiceManagement\app\Enums\AiModelProvider;
use Modules\AiServiceManagement\app\Models\AiCallType;
use Modules\AiServiceManagement\app\Models\AiResponseType;
use Modules\Auth\app\Models\User;
use Modules\ProjectManagement\app\Enums\DataType;
use Modules\ProjectManagement\app\Enums\OutputLanguageStatus;
use Modules\ProjectManagement\app\Enums\ProjectOutputFormat;
use Modules\ProjectManagement\app\Models\OutputLanguage;
use Modules\ProjectManagement\app\Models\Project;
use Modules\ProjectManagement\app\Models\ProjectObjectiveQuestion;
use Override;

final class ProjectRequest extends BaseApiRequest
{
    protected bool $forEdit = false;

    protected int $currentStep = 4;

    protected ?Project $project = null;

    public function forEdit(bool $forEdit = true): self
    {
        $this->forEdit = $forEdit;

        return $this;
    }

    public function forStep(int $step): self
    {
        $this->currentStep = $step;

        return $this;
    }

    public function authorize(): bool
    {
        return $this->user()?->can('manage-projects') ?? false;
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        $this->forEdit = $this->isMethod('PUT');
        if ($this->forEdit) {
            /** @var User $user */
            $user = $this->user();
            $this->project = Project::query()
                ->allowedForUser($user)
                ->with('aiModel')
                ->where('key', $this->route('project'))
                ->firstOrFail();
        }

        if (is_string($this->route('step'))) {
            $this->currentStep = (int) $this->route('step');
        }

    }

    /**
     * @return array<string,mixed>
     */
    #[Override]
    public function rules(): array
    {
        $rules = [];
        foreach (range(1, $this->currentStep) as $step) {
            $rules = array_merge($rules, $this->{"step{$step}rules"}());
        }

        return $rules;
    }

    /**
     * @return array<string,mixed>
     */
    public function step1Rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                Rule::unique(Project::class, 'name')
                    ->where('user_id', auth()->user()?->id)
                    ->ignore($this->project?->id),
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
                Rule::when(
                    $this->forEdit,
                    [
                        'min:2',
                        'max:3',
                    ],
                    [
                        'size:2',
                    ]
                ),

            ],
            'objective_questions.*.question_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists(ProjectObjectiveQuestion::class, 'id')
                    ->where('status', 1)
                    ->withoutTrashed(),
            ],
            'objective_questions.*.answer_id' => [
                Rule::requiredIf($this->forEdit),
                'integer',
                'min:' . config('global.min_integer'),
                'max:' . config('global.max_integer'),
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
                'integer',
                'filled',
                'distinct',
                Rule::exists(OutputLanguage::class, 'id')
                    ->where('status', OutputLanguageStatus::Enabled)
                    ->withoutTrashed(),
            ],

        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function step2Rules(): array
    {
        $hasAiModelConfiguration = filled($this->input('ai_model_name'))
            || filled($this->input('ai_model_alias'))
            || filled($this->input('ai_model_provider'))
            || filled($this->input('ai_model_api_key'))
            || filled($this->input('ai_model_connector_url'));

        return [
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
            'ai_temperature' => [
                'required',
                'numeric',
                'between:0,2',
            ],
            'ai_system_prompt' => [
                'nullable',
                'string',
                'max:20000',
            ],
            'ai_max_output_tokens' => [
                'sometimes',
                'integer',
                'min:1',
                'max:32768',
            ],
            'ai_response_schema' => [
                'nullable',
                'array',
            ],
            'ai_model_name' => [
                Rule::requiredIf($hasAiModelConfiguration),
                'nullable',
                'string',
                'max:255',
            ],
            'ai_model_alias' => [
                Rule::requiredIf($hasAiModelConfiguration),
                'nullable',
                'string',
                'max:255',
            ],
            'ai_model_provider' => [
                Rule::requiredIf($hasAiModelConfiguration),
                'nullable',
                Rule::enum(AiModelProvider::class),
            ],
            'ai_model_api_key' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'ai_model_connector_url' => [
                Rule::requiredIf(
                    fn (): bool => $this->integer('ai_model_provider') === AiModelProvider::OpenAiCompatible->value
                ),
                'nullable',
                'string',
                'url:https',
                'max:2048',
            ],
        ];
    }

    /**
     * @return array<string,mixed>
     */
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
            'project_inputs.*.id' => [
                'missing_if:' . $this->forEdit . ',false',
                'integer',
                'min:' . config('global.min_integer'),
                'max:' . config('global.max_integer'),
                'distinct',
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
                'max:' . config('global.max_text_length'),
            ],
            'project_inputs.*.values' => [
                'sometimes',
            ],
        ];
    }

    /**
     * @return array<string,mixed>
     */
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
            'project_outputs.*.id' => [
                'missing_if:' . $this->forEdit . ',false',
                'integer',
                'min:' . config('global.min_integer'),
                'max:' . config('global.max_integer'),
                'distinct',
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
                'max:' . config('global.max_text_length'),
            ],
            'project_outputs.*.values' => [
                'sometimes',
            ],
        ];
    }

    protected function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->count()) {
                $validator->errors()->add(
                    key: 'failed_step',
                    message: (string) $this->determineFailedRuleStep(
                        failedRule: Arr::first($validator->errors()->keys())//@phpstan-ignore-line
                    )
                );
            }
        });
    }

    protected function determineFailedRuleStep(string $failedRule): ?int
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

    public function getProject(): ?Project
    {
        return $this->project;
    }
}
