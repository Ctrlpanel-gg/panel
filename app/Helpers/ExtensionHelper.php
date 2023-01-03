<?php

namespace App\Helpers;

class ExtensionHelper
{
    public static function getExtensionConfig($extensionName, $nameSpace)
    {
        $extension = app_path() . '/Extensions/' . $nameSpace . "/" . $extensionName . "/index.php";
        // Check if extension exists
        if (!file_exists($extension)) {
            return null;
        }

        // call the getConfig function from the index.php file of the extension
        $config = include_once $extension;

        // Check if the getConfig function exists
        if (!function_exists('getConfig')) {
            return null;
        }

        $config = call_user_func('getConfig');

        // Check if the getConfig function returned an array
        if (!is_array($config)) {
            return null;
        }

        return $config;
    }
}