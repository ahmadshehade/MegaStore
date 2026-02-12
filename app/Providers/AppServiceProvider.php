<?php

namespace App\Providers;

use App\Enum\UserRoles;
use FFI;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define("admin-job", function ($user) {
            return $user->hasRole(UserRoles::SuperAdmin->value);
        });
        Gate::define('seller-job', function ($user) {
            return $user->hasRole(UserRoles::Seller->value);
        });
        Gate::define('customer-job', function ($user) {
            return $user->hasRole(UserRoles::Customer->value);
        });
    }
}
