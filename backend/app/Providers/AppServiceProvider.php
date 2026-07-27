<?php

namespace App\Providers;

use App\Models\Experience;
use App\Policies\ExperiencePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Password::defaults(static fn () => Password::min(8)->mixedCase()->numbers());
        Gate::policy(Experience::class, ExperiencePolicy::class);
    }
}
