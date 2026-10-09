<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Finder\Finder;
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

                // Load Web Routes
                $webRoutesFile = $extensionDirectory . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
                if (is_file($webRoutesFile)) {
                    $resolvedPath = realpath($webRoutesFile);
                    $basePath = realpath($extensionsBasePath);
                    if ($resolvedPath && $basePath && str_starts_with($resolvedPath, $basePath . DIRECTORY_SEPARATOR)) {
                        $this->loadRoutesFrom($resolvedPath);
                    }
                }

                // Load API Routes
                $apiRoutesFile = $extensionDirectory . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'api.php';
                if (is_file($apiRoutesFile)) {
                    $resolvedPath = realpath($apiRoutesFile);
                    $basePath = realpath($extensionsBasePath);
                    if ($resolvedPath && $basePath && str_starts_with($resolvedPath, $basePath . DIRECTORY_SEPARATOR)) {
                        $this->loadExtensionApiRoutes($resolvedPath);
                    }
                }

                // Load Views
                $viewsDirectory = $extensionDirectory . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
                if (is_dir($viewsDirectory)) {
                    $viewNamespace = Str::lower($namespaceName . '_' . $extensionName);
                    $this->loadViewsFrom($viewsDirectory, $viewNamespace);
                }

                // Load Translations
                $langDirectory = $extensionDirectory . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang';
                if (is_dir($langDirectory)) {
                    $this->loadJsonTranslationsFrom($langDirectory);
                    $this->loadTranslationsFrom($langDirectory, Str::lower($extensionName));
                }

                // Load Migrations
                $migrationsDirectory = $extensionDirectory . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
                if (is_dir($migrationsDirectory)) {
                    $this->loadMigrationsFrom($migrationsDirectory);
                }

                // Load Artisan Commands
                $commandsDirectory = $extensionDirectory . DIRECTORY_SEPARATOR . 'Console' . DIRECTORY_SEPARATOR . 'Commands';
                if (is_dir($commandsDirectory) && $this->app->runningInConsole()) {
                    $this->loadCommandsFromDirectory($commandsDirectory, "App\\Extensions\\{$namespaceName}\\{$extensionName}\\Console\\Commands");
                }
            }
        }

        // Register Database Seeders
        if ($this->app->runningInConsole()) {
            foreach (\App\Helpers\ExtensionHelper::getAllExtensionSeeders() as $seederClass) {
                $this->app->singleton($seederClass);
            }
        }

        // Register Extension Middleware
        $this->registerExtensionMiddleware();

        // Boot Extension Schedules
        if ($this->app->runningInConsole()) {
            $this->app->booted(function () {
                $schedule = $this->app->make(Schedule::class);
                $this->scheduleExtensions($schedule);
            });
        }
    }

    /**
     * Load an extension's API routes with the api middleware group and prefix,
     * mirroring how the application's core API routes are registered.
     */
    protected function loadExtensionApiRoutes(string $path): void
    {
        $this->callAfterResolving('router', function ($router) use ($path) {
            $router->group(['prefix' => 'api', 'middleware' => 'api', 'name' => 'api.'], function ($router) use ($path) {
                require $path;
            });
        });
    }

    /**
     * Register middleware declared by extensions, either globally, in a middleware
     * group, or as a route middleware alias.
     */
    protected function registerExtensionMiddleware(): void
    {
        $router = $this->app->make(\Illuminate\Routing\Router::class);
        $kernel = $this->app->make(\Illuminate\Foundation\Http\Kernel::class);

        foreach (\App\Helpers\ExtensionHelper::getAllExtensionMiddleware() as $middleware) {
            $class = $middleware['class'];
            $position = $middleware['position'];

            if ($middleware['global']) {
                if ($position === 'prepend') {
                    $kernel->prependMiddleware($class);
                } else {
                    $kernel->pushMiddleware($class);
                }
            }

            foreach ($middleware['groups'] as $group) {
                if ($position === 'prepend') {
                    $router->prependMiddlewareToGroup($group, $class);
                } else {
                    $router->pushMiddlewareToGroup($group, $class);
                }
            }

            if ($middleware['alias'] !== null) {
                $router->aliasMiddleware($middleware['alias'], $class);
            }
        }
    }

    /**
     * Automatically discover and register Artisan commands from an extension's Commands directory.
     */
    protected function loadCommandsFromDirectory(string $directory, string $namespace): void
    {
        $finder = (new Finder())->in($directory)->files()->name('*.php');

        foreach ($finder as $file) {
            $class = $namespace . '\\' . str_replace(['/', '.php'], ['\\', ''], $file->getRelativePathname());

            if (is_subclass_of($class, \Illuminate\Console\Command::class) && !(new \ReflectionClass($class))->isAbstract()) {
                $this->commands([$class]);
            }
        }
    }

    /**
     * Dispatch schedule registration to any extension defining a schedule() method on its main class or scheduler handler.
     */
    protected function scheduleExtensions(Schedule $schedule): void
    {
        if (class_exists(\App\Helpers\ExtensionHelper::class)) {
            $extensionClasses = \App\Helpers\ExtensionHelper::getAllExtensionClasses();
            foreach ($extensionClasses as $extensionClass) {
                if (method_exists($extensionClass, 'schedule')) {
                    $extensionClass::schedule($schedule);
                }
            }
        }
    }
}
