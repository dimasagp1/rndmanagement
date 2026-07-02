<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use App\Models\Formula;
use App\Models\User;
use App\Policies\FormulaPolicy;
use App\Services\FormulaService;
use App\Models\TrialRm;
use App\Policies\TrialRmPolicy;
use App\Services\TrialRmService;
use App\Models\TrialPm;
use App\Policies\TrialPmPolicy;
use App\Services\TrialPmService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Require global settings helper
        require_once app_path('Helpers/setting.php');

        // Bind Services sebagai singleton
        $this->app->singleton(FormulaService::class);
        $this->app->singleton(TrialRmService::class);
        $this->app->singleton(TrialPmService::class);
    }

    public function boot(): void
    {
        // Register Policies
        Gate::policy(Formula::class, FormulaPolicy::class);
        Gate::policy(TrialRm::class, TrialRmPolicy::class);
        Gate::policy(TrialPm::class, TrialPmPolicy::class);

        // Implicitly grant "Superadmin" role all permission checks
        Gate::before(function ($user, $ability) {
            return $user->hasRole('Superadmin') ? true : null;
        });

        // Share pending approval count ke semua view layout
        View::composer('layouts.app', function ($view) {
            if (! Auth::check()) {
                return;
            }

            $user = Auth::user();
            $notifCount = 0;

            if ($user->hasRole('Operational Manager')) {
                $notifCount = Formula::where('approval_status', 'Pending Tahap 1')->count();
            } elseif ($user->hasRole('General Manager')) {
                $notifCount = Formula::where('approval_status', 'Pending Tahap 2')->count();
            }

            $view->with('navNotifCount', $notifCount);
        });
    }
}
