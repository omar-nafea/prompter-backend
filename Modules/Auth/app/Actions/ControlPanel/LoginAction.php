<?php

declare(strict_types=1);

namespace Modules\Auth\app\Actions\ControlPanel;

use App\Enums\OtpTypes;
use App\Enums\PlatformType;
use Closure;
use DB;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Auth\app\Dtos\ControlPanel\LoginDto;
use Modules\Auth\app\Enums\UserIdentifierType;
use Modules\Auth\app\Exceptions\LoginException;
use Modules\Auth\app\Models\User;
use Symfony\Component\HttpFoundation\Response as ResponseStatusCode;

final class LoginAction
{
    public function __construct(
    ) {}

    /**
     * @throws LoginException
     */
    public function execute(LoginDto $dto): array
    {
        return DB::transaction(fn() => Pipeline::send(['dto' => $dto])
            ->through([
                $this->ensureIsNotRateLimited(...),
                $this->hitTheRateLimiter(...),
                $this->getUser(...),
                $this->ensureUserIsActive(...),
                $this->createAuthAndRefreshTokens(...),
                //                $this->markUserAsUnverified(...),
                //                $this->generateOtp(...),
                //                $this->sendOtpNotification(...),
                $this->clearRateLimiter(...),
            ])->thenReturn());
    }

    protected function ensureIsNotRateLimited(array $params, Closure $next)
    {

        /* @var LoginDto $dto */
        $dto = $params['dto'];

        if ( ! RateLimiter::tooManyAttempts($this->throttleKey($dto), 5)) {
            return $next($params);
        }

        event(new Lockout(request()));

        $seconds = RateLimiter::availableIn($this->throttleKey($dto));

        throw ValidationException::withMessages([
            'email' => __('auth::login.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ])->status(ResponseStatusCode::HTTP_TOO_MANY_REQUESTS);

    }

    protected function hitTheRateLimiter(array $params, Closure $next)
    {
        /* @var LoginDto $dto */
        $dto = $params['dto'];
        RateLimiter::hit($this->throttleKey($dto));

        return $next($params);
    }

    protected function throttleKey(LoginDto $dto): string
    {
        return md5(
            Str::transliterate(
                Str::lower(
                    $dto->email->toNative()
                )
                . '|login|' .
                request()->ip()
            )
        );
    }

    /**
     * @throws LoginException
     */
    protected function getUser(array $params, Closure $next)
    {
        /* @var LoginDto $dto */
        $dto = $params['dto'];

        $params['user'] = User::where('email', $dto->email)->first();

        if ( ! $params['user'] || ! Hash::check($dto->password, $params['user']->password)) {
            throw LoginException::invalidCredentials();
        }

        return $next($params);
    }

    protected function ensureUserIsActive(array $params, Closure $next)
    {
        /* @var LoginDto $dto */
        $dto = $params['dto'];

        /* @var User $user */
        $user = $params['user'];

        if ( ! $user->status->isActive()) {
            throw ValidationException::withMessages(
                [
                    'email' => __('auth::login.user_not_active'),
                ]
            );
        }

        return $next($params);
    }

    protected function createAuthAndRefreshTokens(array $params, Closure $next)
    {
        /* @var LoginDto $dto */
        $dto = $params['dto'];
        /* @var User $user */
        $user = $params['user'];
        $params['authToken'] = $user->createAuthToken('ss-auth');
        $params['refreshToken'] = $user->createRefreshToken('ss-refresh');

        return $next($params);
    }

    protected function markUserAsUnverified(array $params, Closure $next)
    {
        return $next($params);
        /* @var LoginDto $dto */
        $dto = $params['dto'];
        /* @var User $user */
        $user = $params['user'];

        cache()->put('last_login_by', $dto->userIdentifierType->value);
        $this->markUserAsUnVerifiedAction->execute(
            user: $user,
            platformType: PlatformType::Angular_Control_Panel,
            userIdentifierType: $dto->userIdentifierType
        );
        //        if ($dto->userIdentifierType === UserIdentifierType::Email) {
        //            $user->update(['email_verified_at' => null]);
        //        } elseif ($dto->userIdentifierType === UserIdentifierType::Phone) {
        //            $user->update(['phone_number_validated_at' => null]);
        //        }

        return $next($params);
    }

    protected function generateOtp(array $params, Closure $next)
    {
        return $next($params);
        /** @var User $user */
        $user = $params['user'];
        $params['otp'] = $this->generateOtpAction->execute(
            user: $user,
            otpType: OtpTypes::FOR_LOGIN,
            email: $user->email,
            phoneNumber: $user->phone_number
        )->code;

        return $next($params);
    }

    protected function sendOtpNotification(array $params, Closure $next)
    {
        $this->sendOtpNotificationByUserIdentifier->execute(
            dto: $params['dto'],
            user: $params['user'],
            otp: $params['otp']
        );

        return $next($params);
    }

    protected function clearRateLimiter(array $params, Closure $next)
    {
        /* @var LoginDto $dto */
        $dto = $params['dto'];
        RateLimiter::clear($this->throttleKey($dto));

        return $next($params);
    }
}
