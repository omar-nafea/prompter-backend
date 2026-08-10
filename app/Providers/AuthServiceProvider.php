<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Auth\app\Enums\UserRole;
use Modules\Auth\app\Models\User;

final class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [

    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('manage-users', static fn (User $user): bool => $user->role->isSuperAdmin());
        Gate::define(
            'manage-projects',
            static fn (User $user): bool => $user->role->canManageProjects()
        );
        Gate::define(
            'manage-ai-settings',
            static fn (User $user): bool => $user->role !== UserRole::Tester
        );
    }
}
