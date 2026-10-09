<?php

namespace App\Classes;

abstract class AbstractExtension
{
    abstract public static function getConfig(): array;

    /**
     * Get the sidebar pages this extension wants to add.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getSidebarPages(): array
    {
        return [];
    }

    /**
     * Get the permissions this extension registers with the role system.
     *
     * @return array<string, string>
     */
    public static function getPermissions(): array
    {
        return [];
    }

    /**
     * Get the middleware this extension wants to register with the application.
     *
     * @return array<int, array<string, mixed>>
     */
    public static function getMiddleware(): array
    {
        return [];
    }
}
