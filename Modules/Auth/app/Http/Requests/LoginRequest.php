<?php

declare(strict_types=1);

namespace Modules\Auth\app\Http\Requests;

use App\Http\Requests\BaseApiRequest;

final class LoginRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc,strict',
                'max:' . config('global.max_string_length'),
            ],
            'password' => [
                'required',
                'max:' . config('global.max_string_length'),
            ],
        ];
    }
}
