<?php

declare(strict_types=1);

namespace Modules\Auth\app\Actions;

use Illuminate\Support\Facades\Password;
use Modules\Auth\app\Dtos\ForgetPasswordDto;
use Modules\Auth\app\Exceptions\ForgetPasswordException;

final class ForgetPasswordAction
{
    public function execute(ForgetPasswordDto $dto): void
    {
        //todo send in queue
        $status = Password::sendResetLink([
            'email' => $dto->email->toNative(),
        ]);
        if ($status === Password::RESET_THROTTLED) {
            throw ForgetPasswordException::throttledSendingPasswordResetLink($status);
        }
        //                if ($status !== Password::RESET_LINK_SENT) {
        //                    throw ForgetPasswordException::failedSendingPasswordResetLink($status);
        //        }
    }
}
