<?php

namespace App\Providers;

use App\Models\DocumentRequirement;
use App\Models\User;
use App\Models\Verification;
use App\Models\VerificationChecklist;
use App\Policies\DocumentRequirementPolicy;
use App\Policies\UserPolicy;
use App\Policies\VerificationChecklistPolicy;
use App\Policies\VerificationPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(Verification::class, VerificationPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(DocumentRequirement::class, DocumentRequirementPolicy::class);
        Gate::policy(VerificationChecklist::class, VerificationChecklistPolicy::class);

        Gate::define('admin', fn (User $user) => $user->isAdmin());
        Gate::define('verifikator', fn (User $user) => $user->isVerifikator());
        Gate::define('pimpinan', fn (User $user) => $user->isPimpinan());
        Gate::define('access-admin', fn (User $user) => $user->canAccessAdmin());
        Gate::define('manage-users', fn (User $user) => $user->isAdmin());
        Gate::define('manage-document-requirements', fn (User $user) => $user->isAdmin());
        Gate::define('export-reports', fn (User $user) => ($user->isAdmin() || $user->isPimpinan()) && $user->is_active);
        Gate::define('view-audit-logs', fn (User $user) => ($user->isAdmin() || $user->isPimpinan()) && $user->is_active);

        RateLimiter::for('status-check', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}
