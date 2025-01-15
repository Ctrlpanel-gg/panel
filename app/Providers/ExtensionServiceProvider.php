<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ExtensionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
       $this->loadRoutesFrom(base_path('routes/extensions.php'));
    }
}
