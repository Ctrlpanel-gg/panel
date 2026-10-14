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

                // Web Routes
                $webRoutesFile = $extensionDirectory . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'web.php';
                if (is_file($webRoutesFile)) {
                    $resolvedPath = realpath($webRoutesFile);
                    $basePath = realpath($extensionsBasePath);
                    if ($resolvedPath && $basePath && str_starts_with($resolvedPath, $basePath . DIRECTORY_SEPARATOR)) {
                        $this->loadRoutesFrom($resolvedPath);
                    }
                }

                // API Routes
                $apiRoutesFile = $extensionDirectory . DIRECTORY_SEPARATOR . 'routes' . DIRECTORY_SEPARATOR . 'api.php';
                if (is_file($apiRoutesFile)) {
                    $resolvedPath = realpath($apiRoutesFile);
                    $basePath = realpath($extensionsBasePath);
                    if ($resolvedPath && $basePath && str_starts_with($resolvedPath, $basePath . DIRECTORY_SEPARATOR)) {
                        $this->loadExtensionApiRoutes($resolvedPath);
                    }
                }

                // Views
                $viewsDirectory = $extensionDirectory . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'views';
                if (is_dir($viewsDirectory)) {
                    $viewNamespace = Str::lower($namespaceName . '_' . $extensionName);
                    $this->loadViewsFrom($viewsDirectory, $viewNamespace);
                }

                // Translations
                $langDirectory = $extensionDirectory . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'lang';
                if (is_dir($langDirectory)) {
                    $this->loadJsonTranslationsFrom($langDirectory);
                    $this->loadTranslationsFrom($langDirectory, Str::lower($extensionName));
                }

                // Migrations
                $migrationsDirectory = $extensionDirectory . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
                if (is_dir($migrationsDirectory)) {
                    $this->loadMigrationsFrom($migrationsDirectory);
                }

                // Artisan Commands
                $commandsDirectory = $extensionDirectory . DIRECTORY_SEPARATOR . 'Console' . DIRECTORY_SEPARATOR . 'Commands';
                if (is_dir($commandsDirectory) && $this->app->runningInConsole()) {
                    $this->loadCommandsFromDirectory($commandsDirectory, "App\\Extensions\\{$namespaceName}\\{$extensionName}\\Console\\Commands");
                }
            }
        }

        // Database Seeders
        if ($this->app->runningInConsole()) {
            foreach (\App\Helpers\ExtensionHelper::getAllExtensionSeeders() as $seederClass) {
                $this->app->singleton($seederClass);
            }
        }

        // Extension Middleware
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
     * Extension API routes under the api group and prefix.
     */
    protected function loadExtensionApiRoutes(string $path): void
    {
        $this->callAfterResolving('router', function ($router) use ($path) {
            $router->group(['prefix' => 'api', 'middleware' => 'api', 'as' => 'api.'], function ($router) use ($path) {
                require $path;
            });
        });
    }

    /**
     * Middleware declared by extensions.
     */
    protected function registerExtensionMiddleware(): void
    {
        $router = $this->app->make(\Illuminate\Routing\Router::class);
        /** @var \Illuminate\Foundation\Http\Kernel $kernel */
        $kernel = $this->app->make(\Illuminate\Contracts\Http\Kernel::class);

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
                $existingAliases = $router->getMiddleware();
                if (array_key_exists($middleware['alias'], $existingAliases)) {
                    \Illuminate\Support\Facades\Log::warning('Skipped extension middleware alias already taken.', [
                        'alias' => $middleware['alias'],
                        'class' => $class,
                    ]);

                    continue;
                }

                $router->aliasMiddleware($middleware['alias'], $class);
            }
        }
    }

    /**
     * Extension Artisan commands.
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
