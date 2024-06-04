<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Modules\ProjectManagement\app\Enums\DataType;
use Validator;

class ValidateInputOutputEnumValues implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_array($value)) {
            $fail('The :attribute must be an array.');

            return;
        }
        if (! isset($value['data_type'])) {
            $fail('The data type is missing for :attribute.');

            return;
        }
        if (DataType::from($value['data_type']) !== DataType::Enum) {
            if (isset($value['values'])) {
                $fail('The enum values must be missing for :attribute.');

                return;
            }

            return;
        }
        if (! isset($value['values'])) {
            $fail('The enum values are missing for :attribute.');

            return;
        }
        if (! is_array($value['values'])) {
            $fail('The enum values must be an array for :attribute.');

            return;
        }
        Validator::make(['values' => $value['values']], [
            'values.*' => [
                'required',
                'string',
                'distinct',
                'max:' . config('global.max_string_length'),
            ],
        ])->validate();
    }
}
