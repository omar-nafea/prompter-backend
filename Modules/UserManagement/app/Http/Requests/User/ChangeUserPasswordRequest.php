<?php

declare(strict_types=1);

namespace Modules\UserManagement\app\Http\Requests\User;

use App\Http\Requests\BaseApiRequest;
use Modules\Auth\app\Models\User;
use Override;

final class ChangeUserPasswordRequest extends BaseApiRequest
{
    protected User $targetUser;

    public function authorize(): bool
    {
        return $this->user()?->can('manage-users') ?? false;
    }

    #[Override]
    protected function prepareForValidation(): void
    {
        $this->targetUser = User::query()->findOrFail($this->route('user'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'confirmed',
                'min:' . config('global.min_password_length'),
                'max:' . config('global.max_string_length'),
            ],
        ];
    }

    public function getTargetUser(): User
    {
        return $this->targetUser;
    }
}
