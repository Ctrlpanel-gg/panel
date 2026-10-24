<?php

namespace App\Classes;

abstract class AbstractExtension
{
    abstract public static function getConfig(): array;

    /**
     * Sidebar pages added by the extension.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getSidebarPages(): array
    {
        return [];
    }

    /**
     * Permissions registered with the role system.
     *
     * @return array<string, string>
     */
    public static function getPermissions(): array
    {
        return [];
    }

    /**
     * Middleware registered with the application.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getMiddleware(): array
    {
        return [];
    }
}
