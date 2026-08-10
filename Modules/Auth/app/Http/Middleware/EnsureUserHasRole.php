<?php

declare(strict_types=1);

namespace Modules\Auth\app\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Modules\Auth\app\Enums\UserRole;
use Modules\Auth\app\Models\User;

final class EnsureUserHasRole
{
    /**
     * @param  Closure(Request): mixed  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        /** @var ?User $user */
        $user = $request->user();
        $allowed = array_map(
            static fn (string $role): UserRole => UserRole::from((int) $role),
            $roles,
        );

        if ( ! $user || ! in_array($user->role, $allowed, true)) {
            return apiError()
                ->message('You are not authorized to perform this action.')
                ->statusCode(403)
                ->withMeta([
                    'exception' => [
                        'id' => 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
                        'name' => 'UnauthorizedRoleException',
                    ],
                ]);
        }

        return $next($request);
    }
}
