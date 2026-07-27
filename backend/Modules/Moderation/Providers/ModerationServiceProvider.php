<?php

namespace Modules\Moderation\Providers;

use Illuminate\Support\ServiceProvider;

class ModerationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Domain HTTP routes are versioned in routes/api.php.
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }

    public function register(): void
    {
        //
    }
}
