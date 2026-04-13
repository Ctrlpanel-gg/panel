<?php

namespace App\Helpers;

use App\Classes\AbstractExtension;
use Illuminate\Support\Facades\Log;
use Spatie\LaravelSettings\Settings;
use Throwable;

/**
 * Summary of ExtensionHelper
 */
class ExtensionHelper
{
    private const VALID_SEGMENT_PATTERN = '/^[A-Za-z][A-Za-z0-9_]*$/';

    private const CSRF_ALLOWED_PREFIXES = [
        'payment/',
        'extensions/',
    ];

    private static ?array $cachedExtensions = null;

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
     * Summary of getAllExtensionMigrations
     * @return array of all migration paths look like: app/Extensions/ExtensionNamespace/ExtensionName/migrations/
     */
    public static function getAllExtensionMigrations(): array
    {
        $migrations = [];

        foreach (self::discoverExtensions() as $extension) {
            $migrationPath = $extension['absolute_path'] . DIRECTORY_SEPARATOR . 'migrations';
            if (!is_dir($migrationPath)) {
                continue;
            }

            $migrations[] = $migrationPath;
        }

        return array_values(array_unique($migrations));
    }

    /**
     * Summary of getAllExtensionSettings
     * @return array of all setting classes look like: App\Extensions\PaymentGateways\PayPal\PayPalSettings
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
