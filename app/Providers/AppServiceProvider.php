<?php

namespace App\Providers;

use App\Enum\UserRoles;
use App\Interfaces\BaseInterface;
use App\Models\User;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BaseInterface::class,BaseService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // admin-job
        Gate::define('admin-job',function(User $user)  {
            return $user->hasRole(UserRoles::SuperAdmin->value);
        });
    }
}
