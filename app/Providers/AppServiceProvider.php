<?php

namespace App\Providers;

use App\Models\Activity;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;
use App\Models\User;
use App\Models\Program;
use App\Models\Application;
use App\Policies\ProgramPolicy;
use App\Policies\ActivityPolicy;
use App\Policies\ApplicationPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadApiRoutes();
        $this->registerPolicies();
        
        ResetPassword::createUrlUsing(function (User $user, string $token) {
            return 'http://localhost:8080/reset-password?token='.$token;
        });
    }
    
    protected function loadApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->group(base_path('routes/api.php'));
    }

    protected function registerPolicies()
    {
        // Register the policies
        \Illuminate\Support\Facades\Gate::policy(Program::class, ProgramPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(Activity::class, ActivityPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(Application::class, ApplicationPolicy::class);
    }
}
