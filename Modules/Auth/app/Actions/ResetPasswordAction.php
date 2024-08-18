<?php

declare(strict_types=1);

namespace Modules\Auth\app\Actions;

use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Modules\Auth\app\Dtos\ResetPasswordDto;
use Modules\Auth\app\Exceptions\ResetPasswordException;
use Modules\Auth\app\Models\User;

final class ResetPasswordAction
{
    public function execute(ResetPasswordDto $dto): void
    {
        $status = Password::reset(
            $dto->toArray(),
            function (User $user, string $password): void {
                $user->forceFill(compact('password'));
                if ( ! $user->email_verified_at) {
                    $user->email_verified_at = now();
                }
                $user->save();
                event(new PasswordReset($user));
            }
        );
        if ($status !== Password::PASSWORD_RESET) {
            throw ResetPasswordException::failedResetPassword($status);
        }
    }
}
