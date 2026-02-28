<?php

namespace App\Providers;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\SuperAdmin\Role\RoleRepositoryInterface::class,
            \App\Repositories\SuperAdmin\Role\RoleRepository::class
        );

        $this->app->bind(
            \App\Repositories\SuperAdmin\Permission\PermissionRepositoryInterface::class,
            \App\Repositories\SuperAdmin\Permission\PermissionRepository::class
        );

        $this->app->bind(
            \App\Repositories\SuperAdmin\Users\UsersRepositoryInterface::class,
            \App\Repositories\SuperAdmin\Users\UsersRepository::class
        );

        $this->app->bind(
            \App\Repositories\Superadmin\GiveAccesTo\GiveAccesToRepositoryInterface::class,
            \App\Repositories\Superadmin\GiveAccesTo\GiveAccesToRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole('superadmin') ? true : null;
        });
    }
}
