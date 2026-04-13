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
        $extensionsBasePath = realpath(app_path('Extensions'));
        if ($extensionsBasePath === false) {
            return;
        }

        $extensionNamespaces = glob($extensionsBasePath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];

        foreach ($extensionNamespaces as $extensionNamespace) {
            $extensions = glob($extensionNamespace . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
            foreach ($extensions as $extension) {
                $routesFile = $extension . DIRECTORY_SEPARATOR . 'routes.php';
                if (!is_file($routesFile)) {
                    continue;
                }

                $resolvedRoutesFile = realpath($routesFile);
                $normalizedBasePath = rtrim($extensionsBasePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
                if ($resolvedRoutesFile === false || !str_starts_with($resolvedRoutesFile, $normalizedBasePath)) {
                    continue;
                }

                $this->loadRoutesFrom($resolvedRoutesFile);
            }
        }
    }
}
