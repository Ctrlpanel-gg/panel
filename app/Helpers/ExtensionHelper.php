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

        $extension = self::getExtensionClass($extensionName);

        $config = $extension::getConfig();



        if (isset($config[$configname])) {
            return $config[$configname];
        }


        return null;
    }

    public static function getAllExtensionClasses()
    {
        $extensions = array_filter(get_declared_classes(), function ($class) {
            $reflection = new \ReflectionClass($class);
            return $reflection->isSubclassOf('App\\Helpers\\AbstractExtension');
        });

        return $extensions;
    }

    public static function getAllExtensionClassesByNamespace(string $namespace)
    {
        $extensions = array_filter(get_declared_classes(), function ($class) use ($namespace) {
            $reflection = new \ReflectionClass($class);
            return $reflection->isSubclassOf('App\\Helpers\\AbstractExtension') && strpos($class, $namespace) !== false;
        });

        return $extensions;
    }



    public static function getExtensionClass(string $extensionName)
    {
        $extensions = self::getAllExtensions();

        foreach ($extensions as $extension) {
            if (!(basename($extension) ==  $extensionName)) {
                continue;
            }

            $extensionClass = $extension . '\\' . $extensionName . 'Extension';
            return $extensionClass;
        }
    }

    public static function getExtension(string $extensionName)
    {
        $extensions = self::getAllExtensions();
        // filter the extensions by the extension name
        $extensions = array_filter($extensions, fn ($item) => basename($item) == $extensionName);

        // return the only extension
        return array_shift($extensions);
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
     * Get all extensions
     * @return array of all extension paths look like: app/Extensions/ExtensionNamespace/ExtensionName
     */
    public static function getAllExtensions()
    {
        $extensions = self::getAllExtensionClasses();
        // remove the last part of the namespace
        $extensions = array_map(fn ($item) => dirname($item), $extensions);

        return $extensions;
    }

    public static function getAllExtensionsByNamespace(string $namespace)
    {
        $extensions = self::getAllExtensionClassesByNamespace($namespace);
        // remove the last part of the namespace
        $extensions = array_map(fn ($item) => dirname($item), $extensions);

        return $extensions;
    }

    /**
     * Summary of getAllExtensionMigrations
     * @return array of all migration paths look like: app/Extensions/ExtensionNamespace/ExtensionName/migrations/
     */
    public static function getAllExtensionMigrations()
    {
        $extensions = self::getAllExtensions();

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

        $settingClass = $extension . '/' . $extensionName . 'Settings';


        if (class_exists($settingClass)) {
            return new $settingClass();
        }
    }
}
