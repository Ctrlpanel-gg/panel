<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
        $extensionsBasePath = realpath(app_path('Extensions'));
        if ($extensionsBasePath === false) {
            return;
        }

        $namespaceDirectories = glob($extensionsBasePath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];

        foreach ($namespaceDirectories as $namespaceDirectory) {
            $namespaceName = basename($namespaceDirectory);
            $extensionDirectories = glob($namespaceDirectory . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];

            foreach ($extensionDirectories as $extensionDirectory) {
                $extensionName = basename($extensionDirectory);

                // Load Routes
                $routesFile = $extensionDirectory . DIRECTORY_SEPARATOR . 'routes.php';
                if (is_file($routesFile)) {
                    $this->loadRoutesFrom($routesFile);
                }

                // Load Views
                $viewsDirectory = $extensionDirectory . DIRECTORY_SEPARATOR . 'views';
                if (is_dir($viewsDirectory)) {
                    $viewNamespace = Str::lower($namespaceName . '_' . $extensionName);
                    $this->loadViewsFrom($viewsDirectory, $viewNamespace);
                }

                // Load Migrations
                $migrationsDirectory = $extensionDirectory . DIRECTORY_SEPARATOR . 'migrations';
                if (is_dir($migrationsDirectory)) {
                    $this->loadMigrationsFrom($migrationsDirectory);
                }
            }
        }
    }
}
