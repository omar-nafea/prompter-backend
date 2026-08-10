<?php

declare(strict_types=1);

namespace Modules\UserManagement\app\Http\Requests\User;

use App\Http\Requests\BaseApiRequest;
use Illuminate\Validation\Rule;
use Modules\Auth\app\Enums\UserRole;

final class CreateUserRequest extends BaseApiRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('manage-users') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
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
                'min:' . config('global.min_password_length'),
                'max:' . config('global.max_string_length'),
            ],
            'phone' => [
                'nullable',
                'string',
                'phone_format',
                Rule::unique('users')->withoutTrashed(),
                'max:' . config('global.max_string_length'),
            ],
            'role' => [
                'required',
                Rule::enum(UserRole::class),
            ],
        ];
    }
}
