<?php

namespace App\Providers;

use App\Models\Formation;
use App\Models\Intervention;
use App\Models\Module;
use App\Models\Promotion;
use App\Models\School;
use App\Models\User;
use App\Policies\FormationPolicy;
use App\Policies\InterventionPolicy;
use App\Policies\ModulePolicy;
use App\Policies\PromotionPolicy;
use App\Policies\SchoolPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
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
        Gate::policy(Formation::class, FormationPolicy::class);
        Gate::policy(Intervention::class, InterventionPolicy::class);
        Gate::policy(Module::class, ModulePolicy::class);
        Gate::policy(Promotion::class, PromotionPolicy::class);
        Gate::policy(School::class, SchoolPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
