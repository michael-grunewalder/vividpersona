<?php

namespace App\Providers;

use App\Http\Middleware\SetTeamContext;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
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
        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by($request->input('email')));

        RateLimiter::for('verification', fn (Request $request) => Limit::perMinute(5)->by($request->user()?->getKey().'|'.$request->ip()));

        Gate::before(function (?User $user): ?bool {
            return $user?->is_super_admin ? true : null;
        });

        /** @var Kernel $kernel */
        $kernel = app()->make(Kernel::class);

        $kernel->addToMiddlewarePriorityBefore(
            SetTeamContext::class,
            SubstituteBindings::class,
        );
    }
}
