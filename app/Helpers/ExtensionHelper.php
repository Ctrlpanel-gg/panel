<?php

namespace App\Helpers;

/**
 * Summary of ExtensionHelper
 */
class ExtensionHelper
{
    /**
     * Get a config of an extension by its name
     * @param string $extensionName
     * @param string $configname
     */
    public static function getExtensionConfig(string $extensionName, string $configname)
    {
        $extensions = ExtensionHelper::getAllExtensions();

        // call the getConfig function of the config file of the extension like that
        // call_user_func("App\\Extensions\\PaymentGateways\\Stripe" . "\\getConfig");
        foreach ($extensions as $extension) {
            if (!(basename($extension) ==  $extensionName)) {
                continue;
            }

            $configFile = $extension . '/config.php';
            if (file_exists($configFile)) {
                include_once $configFile;
                $config = call_user_func('App\\Extensions\\' . basename(dirname($extension)) . '\\' . basename($extension) . "\\getConfig");
            }


            if (isset($config[$configname])) {
                return $config[$configname];
            }
        }

        return null;
    }

    public static function getAllCsrfIgnoredRoutes()
    {
        $extensions = ExtensionHelper::getAllExtensions();

        $routes = [];
        foreach ($extensions as $extension) {
            $configFile = $extension . '/config.php';
            if (file_exists($configFile)) {
                include_once $configFile;
                $config = call_user_func('App\\Extensions\\' . basename(dirname($extension)) . '\\' . basename($extension) . "\\getConfig");
            }

            if (isset($config['RoutesIgnoreCsrf'])) {
                $routes = array_merge($routes, $config['RoutesIgnoreCsrf']);
            }

            // map over the routes and add the extension name as prefix
            $result = array_map(fn ($item) => "extensions/{$item}", $routes);
        }

        return $result;
    }

    /**
     * Get all extensions
     * @return array of all extension paths look like: app/Extensions/ExtensionNamespace/ExtensionName
     */
    public static function getAllExtensions()
    {
        $extensionNamespaces = glob(app_path() . '/Extensions/*', GLOB_ONLYDIR);
        $extensions = [];
        foreach ($extensionNamespaces as $extensionNamespace) {
            $extensions = array_merge($extensions, glob($extensionNamespace . '/*', GLOB_ONLYDIR));
        }

        return $extensions;
    }

    public static function getAllExtensionsByNamespace(string $namespace)
    {
        $extensions = glob(app_path() . '/Extensions/' . $namespace . '/*', GLOB_ONLYDIR);

        return $extensions;
    }

    /**
     * Summary of getAllExtensionMigrations
     * @return array of all migration paths look like: app/Extensions/ExtensionNamespace/ExtensionName/migrations/
     */
    public static function getAllExtensionMigrations()
    {
        $extensions = ExtensionHelper::getAllExtensions();

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
}
