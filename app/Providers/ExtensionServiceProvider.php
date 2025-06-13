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
        $extensionNamespaces = glob(app_path('Extensions/*'), GLOB_ONLYDIR);
        $extensions = [];

        foreach ($extensionNamespaces as $extensionNamespace) {
            $extensions = array_merge($extensions, glob($extensionNamespace . '/*', GLOB_ONLYDIR));
        }

        foreach ($extensions as $extension) {
            $routesFile = $extension . '/routes.php';

            if (file_exists($routesFile)) {
                $this->loadRoutesFrom($routesFile);
            }
        }
    }
}
