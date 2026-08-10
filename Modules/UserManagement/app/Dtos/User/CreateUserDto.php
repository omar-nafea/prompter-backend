<?php

declare(strict_types=1);

namespace Modules\UserManagement\app\Dtos\User;

use App\Dtos\BaseDto;
use App\ValueObjects\Email;
use App\ValueObjects\Phone;
use Modules\Auth\app\Enums\UserRole;
use Modules\UserManagement\app\Http\Requests\User\CreateUserRequest;

final class CreateUserDto extends BaseDto
{
    public function __construct(
        public string $name,
        public Email $email,
        public string $password,
        public UserRole $role,
        public ?Phone $phone = null,
    ) {}

    public static function fromCreateUserRequest(CreateUserRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            name: $validated['name'],
            email: Email::from($validated['email']),
            password: $validated['password'],
            role: UserRole::from((int) $validated['role']),
            phone: filled($validated['phone'] ?? null) ? Phone::from($validated['phone']) : null,
        );
    }
}
