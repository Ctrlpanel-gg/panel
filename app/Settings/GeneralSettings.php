<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{

    public string $main_site;

    public string $credits_display_name;

    public float $initial_user_credits;

    public int $initial_server_limit;

    public string $recaptcha_site_key;

    public string $recaptcha_secret_key;

    public bool $recaptcha_enabled;

    public string $phpmyadmin_url;

    public bool $alert_enabled;

    public string $alert_type;

    public string $alert_message;

    public string $theme;

    //public int $initial_user_role; wait for Roles & Permissions PR.

    public static function group(): string
    {
        return 'general';
    }

    public static function encrypted(): array
    {
        return [
            'recaptcha_site_key',
            'recaptcha_secret_key'
        ];
    }

    public static function validation()
    {
        // create validation rules that can be used in the controller
        return [
            'main_site' => 'required|url',
            'credits_display_name' => 'required|string',
            'initial_user_credits' => 'required|numeric',
            'initial_server_limit' => 'required|numeric',
        ];
    }
}
