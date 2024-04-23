<?php

declare(strict_types=1);

namespace Modules\Exceptions\app\Factory;

use Illuminate\Auth\Access\AuthorizationException as BaseAuthorizationException;
use Illuminate\Auth\AuthenticationException as BaseAuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException as BaseModelNotFoundException;
use Illuminate\Http\Exceptions\PostTooLargeException as BasePostTooLargeException;
use Illuminate\Support\ItemNotFoundException as BaseItemNotFoundException;
use Illuminate\Validation\UnauthorizedException as BaseUnauthorizedException;
use Illuminate\Validation\ValidationException as BaseValidationException;
use Modules\Exceptions\app\Exceptions\AuthenticationException;
use Modules\Exceptions\app\Exceptions\AuthorizationException;
use Modules\Exceptions\app\Exceptions\CannotCreateDataException;
use Modules\Exceptions\app\Exceptions\ItemNotFoundException;
use Modules\Exceptions\app\Exceptions\MethodNotAllowedHttpException;
use Modules\Exceptions\app\Exceptions\ModelNotFoundException;
use Modules\Exceptions\app\Exceptions\NotFoundHttpException;
use Modules\Exceptions\app\Exceptions\PostTooLargeException;
use Modules\Exceptions\app\Exceptions\UnauthorizedException;
use Modules\Exceptions\app\Exceptions\ValidationException;
use Modules\Exceptions\app\Factory\Contract\ExceptionMappingFactory as ExceptionMappingFactoryContract;
use Spatie\LaravelData\Exceptions\CannotCreateData;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException as BaseMethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as BaseNotFoundHttpException;
use Throwable;

class ExceptionMappingFactory implements ExceptionMappingFactoryContract
{
    /**
     * @throws UnauthorizedException
     * @throws NotFoundHttpException
     * @throws ValidationException
     * @throws MethodNotAllowedHttpException
     * @throws Throwable
     * @throws AuthorizationException
     * @throws AuthenticationException
     */
    public function handle(Throwable $exception): void
    {
        match (true) {
            $exception instanceof BaseAuthenticationException => throw new AuthenticationException($exception),
            $exception instanceof BaseAuthorizationException => throw new AuthorizationException($exception),
            $exception instanceof BaseValidationException => throw new ValidationException($exception),
            $exception instanceof BaseMethodNotAllowedHttpException => throw new MethodNotAllowedHttpException($exception),
            $exception instanceof BaseModelNotFoundException => throw new ModelNotFoundException($exception),
            $exception instanceof BaseNotFoundHttpException => throw new NotFoundHttpException($exception),
            $exception instanceof BaseUnauthorizedException => throw new UnauthorizedException($exception),
            $exception instanceof BasePostTooLargeException => throw new PostTooLargeException($exception),
            $exception instanceof CannotCreateData => throw new CannotCreateDataException($exception),
            $exception instanceof BaseItemNotFoundException => throw new ItemNotFoundException($exception),
            default => throw $exception
        };
    }
}
