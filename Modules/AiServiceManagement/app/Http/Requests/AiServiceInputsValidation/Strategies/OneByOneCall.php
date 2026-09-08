<?php

declare(strict_types=1);

namespace Modules\AiServiceManagement\app\Http\Requests\AiServiceInputsValidation\Strategies;

use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
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
     * @return array<string, mixed[]>
     */
    public function rules(): array
    {
        /** @var Collection<string, mixed[]> $rulesCollection */
        $rulesCollection = $this->project->inputs->mapWithKeys(
            callback: fn (ProjectInput $input): array => [
                $input->name => $this->getRules($input),
            ]
        );

        return $rulesCollection->toArray();
    }

    /**
     * @return mixed[]
     */
    protected function getRules(ProjectInput $input): array
    {
        $rules = [];

        if ($input->data_type === DataType::Json) {
            // 'present' (not 'required') so that an empty collection [] passes
            // while a missing key fails; null is rejected explicitly below.
            $rules[] = $input->is_required ? 'present' : 'nullable';
            $rules[] = static function (string $attribute, mixed $value, Closure $fail) use ($input): void {
                if ($value === null) {
                    if ($input->is_required) {
                        $fail("The {$attribute} field is required.");
                    }

                    return;
                }
                if (is_array($value)) {
                    return;
                }
                if (is_string($value)) {
                    $decoded = json_decode($value, true);
                    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                        return;
                    }
                }
                $fail("The {$attribute} must be an array or valid JSON.");
            };
        } elseif ($input->data_type === DataType::Enum) {
            $rules[] = $input->is_required ? 'required' : 'nullable';
            $input->loadMissing('enumValues');
            $allowedValues = $input->enumValues->pluck('value')->filter()->values()->all();

            $rules[] = static function (string $attribute, mixed $value, Closure $fail) use ($allowedValues): void {
                if ($value === null) {
                    return;
                }
                if (is_array($value)) {
                    if (empty($value)) {
                        $fail("The {$attribute} cannot be empty.");

                        return;
                    }
                    foreach ($value as $item) {
                        if (is_array($item) || is_object($item)) {
                            $fail("The selected {$attribute} is invalid.");

                            return;
                        }
                        if ( ! empty($allowedValues) && ! in_array((string) $item, $allowedValues, true)) {
                            $fail("The selected {$attribute} is invalid. Allowed: " . implode(', ', $allowedValues));

                            return;
                        }
                    }
                } elseif (is_object($value)) {
                    $fail("The selected {$attribute} is invalid.");
                } else {
                    if ( ! empty($allowedValues) && ! in_array((string) $value, $allowedValues, true)) {
                        $fail("The selected {$attribute} is invalid. Allowed: " . implode(', ', $allowedValues));
                    }
                }
            };
        } else {
            $rules[] = $input->is_required ? 'required' : 'nullable';
            $rules[] = match ($input->data_type) {
                DataType::String => 'string',
                DataType::Integer => 'integer',
                // Laravel has no 'float' rule; 'numeric' accepts decimals.
                DataType::Float => 'numeric',
                DataType::Boolean => 'boolean',
                default => Str::lower($input->data_type->name),
            };
            if ($input->data_type === DataType::Float && str_ends_with($input->name, '_percentage')) {
                $rules[] = 'min:0';
                $rules[] = 'max:100';
            }
        }

        if ($input->max_length && $input->data_type === DataType::String) {
            $rules[] = 'max:' . $input->max_length;
        }

        return $rules;
    }
}
