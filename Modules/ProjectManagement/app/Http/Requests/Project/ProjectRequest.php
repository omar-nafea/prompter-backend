<?php

declare(strict_types=1);

namespace Modules\ProjectManagement\app\Http\Requests\Project;

use App\Http\Requests\BaseApiRequest;
use App\Models\AiCallType;
use App\Models\AiResponseType;
use App\Models\AiService;
use Illuminate\Validation\Rule;
use Modules\ProjectManagement\app\Enums\DataType;
use Modules\ProjectManagement\app\Models\Project;
use Modules\ProjectManagement\app\Models\ProjectObjectiveQuestion;

class ProjectRequest extends BaseApiRequest
{
    public function rules(): array
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

            'project_inputs' => [
                'required',
                'array',
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
                'required',
                'int',
                'max:' . config('global.max_integer'),
            ],
            'project_inputs.*.description' => [
                'required',
                'string',
                'min:' . config('global.min_string_length'),
                'max:' . config('global.max_string_length'),
            ],

            'project_outputs' => [
                'required',
                'array',
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
                'required',
                'int',
                'max:' . config('global.max_integer'),
            ],
            'project_outputs.*.description' => [
                'required',
                'string',
                'min:' . config('global.min_string_length'),
                'max:' . config('global.max_string_length'),
            ],

        ];
    }
}
