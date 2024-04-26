<?php

declare(strict_types=1);

namespace Modules\Auth\app\Http\Requests;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Validation\Rule;

final class RegisterRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:' . config('global.max_string_length'),
            ],
            'email' => [
                'required',
                'email:rfc,strict',
                Rule::unique('users')->withoutTrashed(),
                'max:' . config('global.max_string_length'),
            ],
            'password' => [
                'required',
                'confirmed',
                'max:' . config('global.max_string_length'),
            ],
            'phone' => [
                'nullable',
                'string',
                'phone_format',
                Rule::unique('users')->withoutTrashed(),
                'max:' . config('global.max_string_length'),
            ],
        ];
    }
}
