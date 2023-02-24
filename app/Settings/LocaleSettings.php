<?php

namespace app\Settings;

use Spatie\LaravelSettings\Settings;

class LocaleSettings extends Settings
{
    public string $available;

    public bool $clients_can_change;

    public string $datatables;

    public string $default;

    public bool $dynamic;
    
    public static function group(): string
    {
        return 'locale';
    }
}