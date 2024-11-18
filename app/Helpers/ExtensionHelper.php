<?php

namespace App\Helpers;

/**
 * Summary of ExtensionHelper
 */
class ExtensionHelper
{
    /**
     * Get all extensions
     * @return array array of all extensions e.g. ["App\Extensions\PayPal", "App\Extensions\Stripe"]
     */
    public static function getAllExtensions()
    {
        $extensionNamespaces = glob(app_path() . '/Extensions/*', GLOB_ONLYDIR);
        $extensions = [];
        foreach ($extensionNamespaces as $extensionNamespace) {
            $extensions = array_merge($extensions, glob($extensionNamespace . '/*', GLOB_ONLYDIR));
        }

        // remove base path from every extension but keep app/Extensions/...
        $extensions = array_map(fn ($item) => str_replace(app_path() . '/', 'App/', $item), $extensions);

        return $extensions;
    }

    /**
     * Get all extensions by namespace
     * @param string $namespace case sensitive namespace of the extension e.g. PaymentGateways
     * @return array array of all extensions e.g. ["App\Extensions\PayPal", "App\Extensions\Stripe"]
     */
    public static function getAllExtensionsByNamespace(string $namespace)
    {
        $extensions = glob(app_path() . '/Extensions/' . $namespace . '/*', GLOB_ONLYDIR);
        // remove base path from every extension but keep app/Extensions/...
        $extensions = array_map(fn ($item) => str_replace(app_path() . '/', 'App/', $item), $extensions);

        return $extensions;
    }

    /**
     * Get an extension by its name
     * @param string $extensionName case sensitive name of the extension e.g. PayPal
     * @return string|null the path of the extension e.g. App\Extensions\PayPal
     */
    public static function getExtension(string $extensionName)
    {
        $extensions = self::getAllExtensions();
        // filter the extensions by the extension name
        $extensions = array_filter($extensions, fn ($item) => basename($item) == $extensionName);

        // return the only extension
        return array_shift($extensions);
    }

    /**
     * Get all extension classes
     * @return array array of all extension classes e.g. ["App\Extensions\PayPal\PayPalExtension", "App\Extensions\Stripe\StripeExtension"]
     */
    public static function getAllExtensionClasses()
    {
        $extensions = self::getAllExtensions();

        // Replace slashes with backslashes and add the gateway name with "Extension" at the end
        $extensions = array_map(function ($item) {
            // Convert to backslashes
            $item = str_replace('/', '\\', $item);
            // Get the last part of the path as the gateway name
            $gatewayName = explode('\\', $item);
            $gatewayName = array_pop($gatewayName);

            // Construct the full class namespace
            return $item . '\\' . $gatewayName . 'Extension';
        }, $extensions);

        // Filter out non-existing extension classes
        $extensions = array_filter($extensions, fn ($item) => class_exists($item));
        return $extensions;
    }

    /**
     * Get all extension classes by namespace
     * @param string $namespace case sensitive namespace of the extension e.g. PaymentGateways
     * @return array array of all extension classes e.g. ["App\Extensions\PayPal\PayPalExtension", "App\Extensions\Stripe\StripeExtension"]
     */
    public static function getAllExtensionClassesByNamespace(string $namespace)
    {
        $extensions = self::getAllExtensionsByNamespace($namespace);

        // replace all slashes with backslashes
        $extensions = array_map(fn ($item) => str_replace('/', '\\', $item), $extensions);
        // add the ExtensionClass to the end of the namespace
        $extensions = array_map(fn ($item) => $item . '\\' . basename($item) . 'Extension', $extensions);
        // filter out non existing extension classes
        $extensions = array_filter($extensions, fn ($item) => class_exists($item));

        return $extensions;
    }

    /**
     * Get the class of an extension by its name
     * @param string $extensionName case sensitive name of the extension e.g. PayPal
     * @return string|null the class name of the extension e.g. App\Extensions\PayPal\PayPalExtension
     */
    public static function getExtensionClass(string $extensionName)
    {
        $extensions = self::getAllExtensions();

        foreach ($extensions as $extension) {
            if (!(basename($extension) ==  $extensionName)) {
                continue;
            }
            $extension = str_replace('/', '\\', $extension);
            $extensionClass = $extension . '\\' . $extensionName . 'Extension';
            return $extensionClass;
        }
    }




    /**
     * Get a config of an extension by its name
     * @param string $extensionName
     * @param string $configname
     */
    public static function getExtensionConfig(string $extensionName, string $configname)
    {

        $extension = self::getExtensionClass($extensionName);

        $config = $extension::getConfig();



        if (isset($config[$configname])) {
            return $config[$configname];
        }


        return null;
    }

    public static function getAllCsrfIgnoredRoutes()
    {
        $extensions = self::getAllExtensionClasses();

        $routes = [];

        foreach ($extensions as $extension) {
            $config = $extension::getConfig();

            if (isset($config['RoutesIgnoreCsrf'])) {
                $routes = array_merge($routes, $config['RoutesIgnoreCsrf']);
            }
        }
        // map over the routes and add the extension name as prefix
        $result = array_map(fn ($item) => "extensions/{$item}", $routes);

        return $result;
    }

    /**
     * Summary of getAllExtensionMigrations
     * @return array of all migration paths look like: app/Extensions/ExtensionNamespace/ExtensionName/migrations/
     */
    public static function getAllExtensionMigrations()
    {
        $extensions = self::getAllExtensions();
        // Transform the extensions to a path
        $extensions = array_map(fn ($item) => self::extensionNameToPath($item), $extensions);

        // get all migration directories of the extensions and return them as array
        $migrations = [];
        foreach ($extensions as $extension) {
            $migrationDir = $extension . '/migrations';
            if (file_exists($migrationDir)) {
                $migrations[] = $migrationDir;
            }
        }

        return $migrations;
    }

    /**
     * Summary of getAllExtensionSettings
     * @return array of all setting classes look like: App\Extensions\PaymentGateways\PayPal\PayPalSettings
     */
    public static function getAllExtensionSettingsClasses()
    {
        $extensions = self::getAllExtensions();


        $settings = [];
        foreach ($extensions as $extension) {
            $extensionName = basename($extension);

            // replace all slashes with backslashes
            $extension = str_replace('/', '\\', $extension);
            $settingsClass = $extension . '\\' . $extensionName . 'Settings';
            if (class_exists($settingsClass)) {
                $settings[] = $settingsClass;
            }
        }

        return $settings;
    }

    public static function getExtensionSettings(string $extensionName)
    {
        $extension = self::getExtension($extensionName);
        // replace all slashes with backslashes
        $extension = str_replace('/', '\\', $extension);

        $settingClass = $extension . '\\' . $extensionName . 'Settings';

        if (class_exists($settingClass)) {
            return new $settingClass();
        }
    }

    /**
     * Transforms a extension name to a path
     * @param string $extensionName e.g. App\Extensions\PaymentGateways\PayPal
     * @return string e.g. C:\xampp\htdocs\laravel\app/Extensions/PaymentGateways/PayPal
     */
    private static function extensionNameToPath(string $extensionName)
    {
        return app_path() . '/' .  str_replace('App/', '', $extensionName);
    }
}
