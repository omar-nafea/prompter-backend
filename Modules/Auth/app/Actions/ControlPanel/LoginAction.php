<?php

declare(strict_types=1);

namespace Modules\Auth\app\Actions\ControlPanel;

use Closure;
use DB;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Pipeline;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\NewAccessToken;
use Modules\Auth\app\Dtos\ControlPanel\LoginDto;
use Modules\Auth\app\Exceptions\LoginException;
use Modules\Auth\app\Models\User;
use Symfony\Component\HttpFoundation\Response as ResponseStatusCode;

final class LoginAction
{
    public function __construct(
    ) {}

    /**
     * @return array{dto: LoginDto, user: User, authToken: NewAccessToken, refreshToken: NewAccessToken}
     *
     * @throws LoginException
     */
    public function execute(LoginDto $dto): array
    {
        /** @var array{dto: LoginDto, user: User, authToken: NewAccessToken, refreshToken: NewAccessToken} */
        return DB::transaction(fn () => Pipeline::send(['dto' => $dto])
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

    /**
     * @param  array{dto: LoginDto}  $params
     * @return array{dto: LoginDto, user: User, authToken: NewAccessToken, refreshToken: NewAccessToken}
     */
    protected function ensureIsNotRateLimited(array $params, Closure $next): array
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

    /**
     * @param  array{dto: LoginDto}  $params
     * @return array{dto: LoginDto, user: User, authToken: NewAccessToken, refreshToken: NewAccessToken}
     */
    protected function hitTheRateLimiter(array $params, Closure $next): array
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
     * @param  array{dto: LoginDto, user: User}  $params
     * @return array{dto: LoginDto, user: User, authToken: NewAccessToken, refreshToken: NewAccessToken}
     *
     * @throws LoginException
     */
    protected function getUser(array $params, Closure $next): array
    {
        /* @var LoginDto $dto */
        $dto = $params['dto'];

        $params['user'] = User::where('email', $dto->email)->first();

        if ( ! $params['user'] || ! Hash::check($dto->password, $params['user']->password)) {
            throw LoginException::invalidCredentials();
        }

        return $next($params);
    }

    /**
     * @param  array{dto: LoginDto, user: User}  $params
     * @return array{dto: LoginDto, user: User, authToken: NewAccessToken, refreshToken: NewAccessToken}
     */
    protected function ensureUserIsActive(array $params, Closure $next): array
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

    /**
     * @param  array{dto: LoginDto, user: User}  $params
     * @return array{dto: LoginDto, user: User, authToken: NewAccessToken, refreshToken: NewAccessToken}
     */
    protected function createAuthAndRefreshTokens(array $params, Closure $next): array
    {
        /* @var LoginDto $dto */
        $dto = $params['dto'];
        /* @var User $user */
        $user = $params['user'];
        $params['authToken'] = $user->createAuthToken('ss-auth');
        $params['refreshToken'] = $user->createRefreshToken('ss-refresh');

        return $next($params);
    }

    /**
     * @param  array{dto: LoginDto, user: User}  $params
     * @return array{dto: LoginDto, user: User, authToken: NewAccessToken, refreshToken: NewAccessToken}
     */
    protected function clearRateLimiter(array $params, Closure $next): array
    {
        /* @var LoginDto $dto */
        $dto = $params['dto'];
        RateLimiter::clear($this->throttleKey($dto));

        return $next($params);
    }
}
