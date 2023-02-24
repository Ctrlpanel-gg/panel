<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $main_site;
    public string $credits_display_name;
    public float $initial_user_credits;
    public int $initial_server_limit;
    public bool $recaptcha_enabled;
    public string $recaptcha_site_key;
    public string $recaptcha_secret_key;
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

    /**
     * Summary of validations array
     * @return array<string, string>
     */
    public static function getValidations()
    {
        return [
            'main_site' => 'nullable|string',
            'credits_display_name' => 'required|string',
            'initial_user_credits' => 'required|numeric',
            'initial_server_limit' => 'required|numeric',
            'recaptcha_enabled' => 'nullable|string',
            'recaptcha_site_key' => 'nullable|string',
            'recaptcha_secret_key' => 'nullable|string',
            'phpmyadmin_url' => 'nullable|string',
            'alert_enabled' => 'nullable|string',
            'alert_type' => 'nullable|string',
            'alert_message' => 'nullable|string',
            'theme' => 'required|string'
        ];
    }

    /**
     * Summary of optionTypes
     * Only used for the settings page
     * @return array<array<'type'|'label'|'description'|'options', string|bool|float|int|array<string, string>>>
     */
    public static function getOptionInputData()
    {
        return [
            'main_site' => [
                'type' => 'string',
                'label' => 'Main Site URL',
                'description' => 'The URL of your main site.'
            ],
            'credits_display_name' => [
                'type' => 'string',
                'label' => 'Credits Display Name',
                'description' => 'The name of the currency used.'
            ],
            'initial_user_credits' => [
                'type' => 'number',
                'label' => 'Initial User Credits',
                'description' => 'The amount of credits a user gets when they register.'
            ],
            'initial_server_limit' => [
                'type' => 'number',
                'label' => 'Initial Server Limit',
                'description' => 'The amount of servers a user can create when they register.'
            ],
            'recaptcha_enabled' => [
                'type' => 'boolean',
                'label' => 'Enable reCAPTCHA',
                'description' => 'Enable reCAPTCHA on the login page.'
            ],
            'recaptcha_site_key' => [
                'type' => 'string',
                'label' => 'reCAPTCHA Site Key',
                'description' => 'The site key for reCAPTCHA.'
            ],
            'recaptcha_secret_key' => [
                'type' => 'string',
                'label' => 'reCAPTCHA Secret Key',
                'description' => 'The secret key for reCAPTCHA.'
            ],
            'phpmyadmin_url' => [
                'type' => 'string',
                'label' => 'phpMyAdmin URL',
                'description' => 'The URL of your phpMyAdmin installation.'
            ],
            'alert_enabled' => [
                'type' => 'boolean',
                'label' => 'Enable Alert',
                'description' => 'Enable an alert to be displayed on the home page.'
            ],
            'alert_type' => [
                'type' => 'select',
                'label' => 'Alert Type',
                'options' => [
                    'primary' => 'Blue',
                    'secondary' => 'Grey',
                    'success' => 'Green',
                    'danger' => 'Red',
                    'warning' => 'Orange',
                    'info' => 'Cyan',
                ],
                'description' => 'The type of alert to display.'
            ],
            'alert_message' => [
                'type' => 'string',
                'label' => 'Alert Message',
                'description' => 'The message to display in the alert.'
            ],
            'theme' => [
                'type' => 'select',
                'label' => 'Theme',
                'options' => [
                    'default' => 'Default',
                    'BlueInfinity' => 'Blue Infinity',
                ],
                'description' => 'The theme to use for the site.'
            ],
        ];
    }
}
