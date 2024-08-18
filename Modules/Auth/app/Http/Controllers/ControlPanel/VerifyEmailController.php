<?php

declare(strict_types=1);

namespace Modules\Auth\app\Http\Controllers\ControlPanel;

use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;
use Modules\Auth\app\Models\User;

final class VerifyEmailController
{
    public function __invoke(Request $request)
    {
        $user = User::findOrFail($request->route('id'));

        if ( ! hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
            return redirect()->away(config('app.frontend_url') . 'email-verification/failed');
        }

        if ( ! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return redirect()->away(config('app.frontend_url') . 'email-verification/success');
    }
}
