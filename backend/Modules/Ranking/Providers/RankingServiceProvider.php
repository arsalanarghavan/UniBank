<?php

namespace Modules\Ranking\Providers;

use Illuminate\Support\ServiceProvider;

class RankingServiceProvider extends ServiceProvider
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
