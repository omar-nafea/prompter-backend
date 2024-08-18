<?php

declare(strict_types=1);

namespace Modules\Auth\app\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified as BaseEnsureEmailIsVerified;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

final class EnsureEmailIsVerified extends BaseEnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  string|null  $redirectToRoute
     * @return Response|RedirectResponse|Responsable|null
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if ( ! $request->user() ||
            ($request->user() instanceof MustVerifyEmail &&
                ! $request->user()->hasVerifiedEmail())) {
            if ($request->expectsJson()) {
                return apiError()->message('Your email address is not verified.')->statusCode(403)->withMeta([
                    'exception' => [
                        'id' => '72e808ef-48b1-4b14-938e-ab24ebfd4a20',
                        'name' => 'EmailNotVerifiedException',
                    ],
                ]);
            }

            return Redirect::guest(URL::route($redirectToRoute ?: 'verification.notice'));
        }

        return $next($request);
    }
}
