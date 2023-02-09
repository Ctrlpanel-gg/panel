<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ServerSettings extends Settings
{
    public int $allocation_limit;

    public bool $creation_enabled;

    public bool $enable_upgrade;

    public bool $charge_first_hour;

    public static function group(): string
    {
        return 'server';
    }
}