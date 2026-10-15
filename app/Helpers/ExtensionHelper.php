<?php

namespace App\Helpers;

use App\Classes\AbstractExtension;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelSettings\Settings;
use Symfony\Component\Finder\Finder;
use Throwable;

/**
 * Summary of ExtensionHelper
 */
class ExtensionHelper
{
    private const VALID_SEGMENT_PATTERN = '/^[A-Za-z][A-Za-z0-9_]*$/';

    private const MIDDLEWARE_ALIAS_PATTERN = '/^[A-Za-z_][A-Za-z0-9_.-]*$/';

    private const MIDDLEWARE_GROUP_PATTERN = '/^[A-Za-z_][A-Za-z0-9_-]*$/';

    private const CSRF_ALLOWED_PREFIXES = [
        'payment/',
        'extensions/',
    ];

    private static ?array $cachedExtensions = null;

    private static array $sidebarRawPagesCache = [];

    /**
     * Get all extensions
     * @return array array of all extensions e.g. ["App\Extensions\PayPal", "App\Extensions\Stripe"]
     */
    public static function getAllExtensions(): array
    {
        return array_values(array_map(
            static fn (array $extension): string => $extension['namespace_path'],
            self::discoverExtensions()
        ));
    }

    /**
     * Get all extensions by namespace
     * @param string $namespace case sensitive namespace of the extension e.g. PaymentGateways
     * @return array array of all extensions e.g. ["App\Extensions\PayPal", "App\Extensions\Stripe"]
     */
    public static function getAllExtensionsByNamespace(string $namespace): array
    {
        if (!self::isValidSegment($namespace)) {
            return [];
        }

        return array_values(array_map(
            static fn (array $extension): string => $extension['namespace_path'],
            array_filter(
                self::discoverExtensions(),
                static fn (array $extension): bool => $extension['namespace'] === $namespace
            )
        ));
    }

    /**
     * Get an extension by its name
     * @param string $extensionName case sensitive name of the extension e.g. PayPal
     * @return string|null the path of the extension e.g. App\Extensions\PayPal
     */
    public static function getExtension(string $extensionName): ?string
    {
        $extension = self::findExtensionByName($extensionName);

        return $extension['namespace_path'] ?? null;
    }

    /**
     * Get all extension classes
     * @return array array of all extension classes e.g. ["App\Extensions\PayPal\PayPalExtension", "App\Extensions\Stripe\StripeExtension"]
     */
    public static function getAllExtensionClasses(): array
    {
        return array_values(array_map(
            static fn (array $extension): string => $extension['class'],
            self::discoverExtensions()
        ));
    }

    /**
     * Get all extension classes by namespace
     * @param string $namespace case sensitive namespace of the extension e.g. PaymentGateways
     * @return array array of all extension classes e.g. ["App\Extensions\PayPal\PayPalExtension", "App\Extensions\Stripe\StripeExtension"]
     */
    public static function getAllExtensionClassesByNamespace(string $namespace): array
    {
        if (!self::isValidSegment($namespace)) {
            return [];
        }

        return array_values(array_map(
            static fn (array $extension): string => $extension['class'],
            array_filter(
                self::discoverExtensions(),
                static fn (array $extension): bool => $extension['namespace'] === $namespace
            )
        ));
    }

    /**
     * Get the class of an extension by its name
     * @param string $extensionName case sensitive name of the extension e.g. PayPal
     * @return string|null the class name of the extension e.g. App\Extensions\PayPal\PayPalExtension
     */
    public static function getExtensionClass(string $extensionName): ?string
    {
        $extension = self::findExtensionByName($extensionName);

        return $extension['class'] ?? null;
    }

    /**
     * Get a config of an extension by its name
     * @param string $extensionName
     * @param string $configname
     */
    public static function getExtensionConfig(string $extensionName, string $configname): mixed
    {
        $extension = self::getExtensionClass($extensionName);
        if (!$extension || !method_exists($extension, 'getConfig')) {
            return null;
        }

        $config = $extension::getConfig();
        if (!is_array($config)) {
            return null;
        }

        return $config[$configname] ?? null;
    }

    public static function getAllCsrfIgnoredRoutes(): array
    {
        $routes = [];

        foreach (self::getAllExtensionClasses() as $extensionClass) {
            if (!method_exists($extensionClass, 'getConfig')) {
                continue;
            }

            $config = $extensionClass::getConfig();
            $ignoreRoutes = is_array($config) ? ($config['RoutesIgnoreCsrf'] ?? null) : null;
            if (!is_array($ignoreRoutes)) {
                continue;
            }

            foreach ($ignoreRoutes as $routePattern) {
                $sanitizedPattern = self::sanitizeCsrfRoutePattern($routePattern);
                if ($sanitizedPattern !== null) {
                    $routes[$sanitizedPattern] = true;
                }
            }
        }

        return array_keys($routes);
    }

    /**
     * All extension migration paths.
     *
     * @return array<int, string>
     */
    public static function getAllExtensionMigrations(): array
    {
        $migrations = [];

        foreach (self::discoverExtensions() as $extension) {
            $migrationPath = $extension['absolute_path'] . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'migrations';
            if (!is_dir($migrationPath)) {
                continue;
            }

            $migrations[] = $migrationPath;
        }

        return array_values(array_unique($migrations));
    }

    /**
     * All extension seeder classes.
     *
     * @return array<int, string>
     */
    public static function getAllExtensionSeeders(): array
    {
        $seeders = [];

        foreach (self::discoverExtensions() as $extension) {
            $seedersDirectory = $extension['absolute_path'] . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'seeders';
            if (!is_dir($seedersDirectory)) {
                continue;
            }

            try {
                $finder = (new Finder())->in($seedersDirectory)->files()->name('*.php');
            } catch (Throwable $exception) {
                Log::warning('Failed to scan seeders directory for extension.', [
                    'extension' => $extension['class'] ?? $extension['namespace_path'],
                    'error' => $exception->getMessage(),
                ]);
                continue;
            }

            foreach ($finder as $file) {
                try {
                    $class = str_replace('/', '\\', $extension['namespace_path']) . '\\database\\seeders\\' . str_replace(['/', DIRECTORY_SEPARATOR], '\\', substr($file->getRelativePathname(), 0, -4));

                    if (!is_subclass_of($class, Seeder::class) || (new \ReflectionClass($class))->isAbstract()) {
                        continue;
                    }
                } catch (Throwable $exception) {
                    Log::warning('Failed to load seeder for extension.', [
                        'extension' => $extension['class'] ?? $extension['namespace_path'],
                        'file' => $file->getRelativePathname(),
                        'error' => $exception->getMessage(),
                    ]);
                    continue;
                }

                $seeders[] = $class;
            }
        }

        return array_values(array_unique($seeders));
    }

    /**
     * All extension settings classes.
     *
     * @return array<int, string>
     */
    public static function getAllExtensionSettingsClasses(): array
    {
        $settings = [];

        foreach (self::discoverExtensions() as $extension) {
            if ($extension['settings_class'] === null) {
                continue;
            }

            $settings[] = $extension['settings_class'];
        }

        return array_values(array_unique($settings));
    }

    public static function getExtensionSettings(string $extensionName): ?Settings
    {
        $extension = self::findExtensionByName($extensionName);
        if ($extension === null || $extension['settings_class'] === null) {
            return null;
        }

        try {
            return new $extension['settings_class']();
        } catch (Throwable $exception) {
            report($exception);
            return null;
        }
    }

    /**
     * Sidebar pages for the given area.
     *
     * @param string $area "user" or "admin".
     * @return array<int, array<string, mixed>>
     */
    public static function getSidebarPages(string $area): array
    {
        if (!in_array($area, ['user', 'admin'], true)) {
            return [];
        }

        $pages = self::getRawSidebarPages($area);
        $request = request();

        return array_map(
            static fn (array $page): array => $page + [
                'active' => $page['route_name'] !== null
                    ? $request->routeIs($page['route_name'])
                    : ($page['external'] === false && $page['url'] !== null && $request->is(trim($page['url'], '/'))),
            ],
            $pages
        );
    }

    /**
     * Raw sidebar defs without per-request state. Cached per process.
     *
     * @param string $area "user" or "admin".
     * @return array<int, array<string, mixed>>
     */
    private static function getRawSidebarPages(string $area): array
    {
        if (isset(self::$sidebarRawPagesCache[$area])) {
            return self::$sidebarRawPagesCache[$area];
        }

        $pages = [];

        foreach (self::getAllExtensionClasses() as $extensionClass) {
            if (!is_callable([$extensionClass, 'getSidebarPages'])) {
                continue;
            }

            try {
                $extensionPages = $extensionClass::getSidebarPages();
            } catch (Throwable $exception) {
                Log::warning('Failed to load sidebar pages for extension.', [
                    'extension' => $extensionClass,
                    'error' => $exception->getMessage(),
                ]);
                continue;
            }

            if (!is_array($extensionPages)) {
                continue;
            }

            foreach ($extensionPages as $page) {
                $normalized = self::normalizeSidebarPage($page);
                if ($normalized !== null && $normalized['area'] === $area) {
                    $pages[] = $normalized;
                }
            }
        }

        usort($pages, static fn (array $a, array $b): int => $a['order'] <=> $b['order']);

        self::$sidebarRawPagesCache[$area] = $pages;

        return $pages;
    }

    /**
     * Sidebar pages the current user may see.
     *
     * @param string $area "user" or "admin".
     * @return array<int, array<string, mixed>>
     */
    public static function getVisibleSidebarPages(string $area): array
    {
        $user = auth()->user();
        if (!$user) {
            return [];
        }

        return array_values(array_filter(
            self::getSidebarPages($area),
            static function (array $page) use ($user): bool {
                if (count($page['permissions']) === 0) {
                    return true;
                }

                return $user->canAny($page['permissions']);
            }
        ));
    }

    /**
     * Extension permissions, keyed by name.
     *
     * @return array<string, string>
     */
    public static function getAllExtensionPermissions(): array
    {
        $permissions = [];

        foreach (self::getAllExtensionClasses() as $extensionClass) {
            if (!is_callable([$extensionClass, 'getPermissions'])) {
                continue;
            }

            try {
                $extensionPermissions = $extensionClass::getPermissions();
            } catch (Throwable $exception) {
                Log::warning('Failed to load permissions for extension.', [
                    'extension' => $extensionClass,
                    'error' => $exception->getMessage(),
                ]);
                continue;
            }

            if (!is_array($extensionPermissions)) {
                continue;
            }

            foreach ($extensionPermissions as $readableName => $permissionName) {
                if (!is_string($permissionName) || $permissionName === '') {
                    continue;
                }

                $permissions[$permissionName] = is_string($readableName) && $readableName !== ''
                    ? $readableName
                    : $permissionName;
            }
        }

        // Sidebar-declared permissions become assignable even without getPermissions().
        foreach (array_merge(self::getSidebarPages('user'), self::getSidebarPages('admin')) as $page) {
            foreach ($page['permissions'] as $permissionName) {
                if (!isset($permissions[$permissionName])) {
                    $permissions[$permissionName] = $permissionName;
                }
            }
        }

        return $permissions;
    }

    /**
     * Extension middleware definitions.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getAllExtensionMiddleware(): array
    {
        $middleware = [];

        foreach (self::getAllExtensionClasses() as $extensionClass) {
            if (!is_callable([$extensionClass, 'getMiddleware'])) {
                continue;
            }

            try {
                $definitions = $extensionClass::getMiddleware();
            } catch (Throwable $exception) {
                Log::warning('Failed to load middleware for extension.', [
                    'extension' => $extensionClass,
                    'error' => $exception->getMessage(),
                ]);
                continue;
            }

            if (!is_array($definitions)) {
                continue;
            }

            foreach ($definitions as $definition) {
                $normalized = self::normalizeMiddlewareDefinition($definition, $extensionClass);
                if ($normalized !== null) {
                    $middleware[] = $normalized;
                }
            }
        }

        return $middleware;
    }

    /**
     * Validate a raw middleware definition.
     *
     * @param mixed $definition
     * @return array<string, mixed>|null
     */
    private static function normalizeMiddlewareDefinition(mixed $definition, string $extensionClass): ?array
    {
        if (!is_array($definition)) {
            self::warnInvalidMiddleware($extensionClass, 'Middleware definition must be an array.');

            return null;
        }

        $class = $definition['class'] ?? null;
        if (!is_string($class) || $class === '' || !class_exists($class) || !method_exists($class, 'handle')) {
            self::warnInvalidMiddleware($extensionClass, 'Middleware class must be a resolvable class with a handle() method.');

            return null;
        }

        $extensionNamespace = substr($extensionClass, 0, strrpos($extensionClass, '\\') + 1);
        if (!str_starts_with($class, $extensionNamespace)) {
            self::warnInvalidMiddleware($extensionClass, sprintf('Middleware class [%s] must live under the extension namespace [%s].', $class, $extensionNamespace));

            return null;
        }

        if (!(new \ReflectionClass($class))->isInstantiable()) {
            self::warnInvalidMiddleware($extensionClass, sprintf('Middleware class [%s] must be instantiable.', $class));

            return null;
        }

        $alias = $definition['alias'] ?? null;
        if ($alias !== null && (!is_string($alias) || preg_match(self::MIDDLEWARE_ALIAS_PATTERN, $alias) !== 1)) {
            self::warnInvalidMiddleware($extensionClass, sprintf('Invalid middleware alias [%s].', is_string($alias) ? $alias : gettype($alias)));

            return null;
        }

        $groups = [];
        foreach ((array) ($definition['groups'] ?? []) as $group) {
            if (!is_string($group) || preg_match(self::MIDDLEWARE_GROUP_PATTERN, $group) !== 1) {
                self::warnInvalidMiddleware($extensionClass, sprintf('Invalid middleware group [%s].', is_string($group) ? $group : gettype($group)));
                continue;
            }

            if (!Route::hasMiddlewareGroup($group)) {
                self::warnInvalidMiddleware($extensionClass, sprintf('Middleware group [%s] does not exist.', $group));
                continue;
            }

            $groups[] = $group;
        }

        $position = (($definition['position'] ?? 'append') === 'prepend') ? 'prepend' : 'append';

        return [
            'class' => $class,
            'alias' => $alias,
            'groups' => array_values(array_unique($groups)),
            'global' => (bool) ($definition['global'] ?? false),
            'position' => $position,
        ];
    }

    private static function warnInvalidMiddleware(string $extensionClass, string $reason): void
    {
        Log::warning('Skipped invalid middleware from extension.', [
            'extension' => $extensionClass,
            'reason' => $reason,
        ]);
    }

    /**
     * Validate a raw sidebar page definition.
     *
     * @param mixed $page
     * @return array<string, mixed>|null
     */
    private static function normalizeSidebarPage(mixed $page): ?array
    {
        if (!is_array($page)) {
            return null;
        }

        $title = $page['title'] ?? null;
        if (!is_string($title) || trim($title) === '') {
            return null;
        }

        $area = $page['area'] ?? 'user';
        if (!in_array($area, ['user', 'admin'], true)) {
            Log::warning('Skipped sidebar page with invalid area.', [
                'title' => $title,
                'area' => is_string($area) ? $area : gettype($area),
            ]);

            return null;
        }

        $icon = is_string($page['icon'] ?? null) && trim($page['icon']) !== ''
            ? $page['icon']
            : 'fas fa-circle';

        $permissions = [];
        if (is_array($page['permissions'] ?? null)) {
            foreach ($page['permissions'] as $permission) {
                if (is_string($permission) && $permission !== '') {
                    $permissions[] = $permission;
                }
            }
        }
        $permissions = array_values(array_unique($permissions));

        $href = null;
        $routeName = null;
        $url = null;

        if (is_string($page['route'] ?? null) && $page['route'] !== '' && Route::has($page['route'])) {
            $routeName = $page['route'];
            $params = is_array($page['route_params'] ?? null) ? $page['route_params'] : [];

            try {
                $href = route($routeName, $params);
            } catch (Throwable $exception) {
                Log::warning('Failed to resolve sidebar page route.', [
                    'route' => $routeName,
                    'error' => $exception->getMessage(),
                ]);
                $href = null;
            }
        }

        if ($href === null && is_string($page['url'] ?? null) && trim($page['url']) !== '') {
            $url = trim($page['url']);
            $lowerUrl = strtolower($url);

            if (str_starts_with($lowerUrl, 'http://') || str_starts_with($lowerUrl, 'https://')) {
                $href = $url;
            } elseif (!str_contains($url, '://')) {
                $url = '/'.ltrim($url, '/');
                $href = $url;
            } else {
                Log::warning('Skipped sidebar page with unsupported URL scheme.', [
                    'title' => $title,
                    'url' => $url,
                ]);
                $url = null;
            }
        }

        if ($href === null) {
            return null;
        }

        $lowerUrl = is_string($url) ? strtolower($url) : '';

        return [
            'title' => $title,
            'icon' => $icon,
            'href' => $href,
            'route_name' => $routeName,
            'url' => $url,
            'external' => str_starts_with($lowerUrl, 'http://') || str_starts_with($lowerUrl, 'https://'),
            'permissions' => $permissions,
            'area' => $area,
            'order' => (int) ($page['order'] ?? 0),
        ];
    }

    private static function discoverExtensions(): array
    {
        if (self::$cachedExtensions !== null) {
            return self::$cachedExtensions;
        }

        $extensions = [];
        $extensionsBasePath = realpath(app_path('Extensions'));
        if ($extensionsBasePath === false) {
            self::$cachedExtensions = [];
            return self::$cachedExtensions;
        }

        $namespaceDirectories = glob($extensionsBasePath . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
        foreach ($namespaceDirectories as $namespaceDirectory) {
            $namespaceName = basename($namespaceDirectory);
            if (!self::isValidSegment($namespaceName)) {
                continue;
            }

            $extensionDirectories = glob($namespaceDirectory . DIRECTORY_SEPARATOR . '*', GLOB_ONLYDIR) ?: [];
            foreach ($extensionDirectories as $extensionDirectory) {
                $extensionName = basename($extensionDirectory);
                if (!self::isValidSegment($extensionName)) {
                    continue;
                }

                $resolvedExtensionPath = realpath($extensionDirectory);
                if ($resolvedExtensionPath === false || !self::isPathInsideBase($resolvedExtensionPath, $extensionsBasePath)) {
                    continue;
                }

                $extensionClass = 'App\\Extensions\\' . $namespaceName . '\\' . $extensionName . '\\' . $extensionName . 'Extension';
                if (!class_exists($extensionClass) || !is_subclass_of($extensionClass, AbstractExtension::class)) {
                    continue;
                }

                $settingsClass = 'App\\Extensions\\' . $namespaceName . '\\' . $extensionName . '\\' . $extensionName . 'Settings';
                if (!class_exists($settingsClass) || !is_subclass_of($settingsClass, Settings::class)) {
                    $settingsClass = null;
                }

                $key = $namespaceName . '/' . $extensionName;
                $extensions[$key] = [
                    'namespace' => $namespaceName,
                    'name' => $extensionName,
                    'namespace_path' => self::pathToNamespacePath($resolvedExtensionPath),
                    'absolute_path' => $resolvedExtensionPath,
                    'class' => $extensionClass,
                    'settings_class' => $settingsClass,
                ];
            }
        }

        ksort($extensions);

        self::$cachedExtensions = array_values($extensions);

        return self::$cachedExtensions;
    }

    private static function sanitizeCsrfRoutePattern(mixed $routePattern): ?string
    {
        if (!is_string($routePattern)) {
            return null;
        }

        $routePattern = ltrim(trim($routePattern), '/');
        if ($routePattern === '' || $routePattern === '*') {
            return null;
        }

        if (str_contains($routePattern, '://')) {
            return null;
        }

        if (!preg_match('/^[A-Za-z0-9_\-\/\*\.]+$/', $routePattern)) {
            return null;
        }

        foreach (self::CSRF_ALLOWED_PREFIXES as $prefix) {
            if (str_starts_with($routePattern, $prefix)) {
                return $routePattern;
            }
        }

        Log::warning('Blocked extension CSRF ignore route outside allowed prefixes.', [
            'route_pattern' => $routePattern,
        ]);

        return null;
    }

    private static function findExtensionByName(string $extensionName): ?array
    {
        if (!self::isValidSegment($extensionName)) {
            return null;
        }

        $matches = array_values(array_filter(
            self::discoverExtensions(),
            static fn (array $extension): bool => $extension['name'] === $extensionName
        ));

        if (count($matches) === 1) {
            return $matches[0];
        }

        if (count($matches) > 1) {
            Log::warning('Multiple extensions found for the same extension name.', [
                'extension_name' => $extensionName,
                'namespaces' => array_map(static fn (array $match): string => $match['namespace'], $matches),
            ]);
        }

        return null;
    }

    private static function isValidSegment(string $segment): bool
    {
        return preg_match(self::VALID_SEGMENT_PATTERN, $segment) === 1;
    }

    private static function isPathInsideBase(string $path, string $basePath): bool
    {
        $normalizedBase = rtrim($basePath, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        return $path === rtrim($basePath, DIRECTORY_SEPARATOR)
            || str_starts_with($path, $normalizedBase);
    }

    private static function pathToNamespacePath(string $absolutePath): string
    {
        $normalizedAppPath = rtrim(str_replace('\\', '/', app_path()), '/');
        $normalizedPath = str_replace('\\', '/', $absolutePath);

        return str_replace($normalizedAppPath . '/', 'App/', $normalizedPath);
    }
}
