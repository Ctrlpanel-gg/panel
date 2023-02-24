<?php

namespace app\Settings;

use Spatie\LaravelSettings\Settings;

class TicketSettings extends Settings
{
    public bool $enabled;
    
    public string $notify;

    public static function group(): string
    {
        return 'ticket';
    }
}