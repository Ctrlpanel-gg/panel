<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public bool $store_enabled;
    public ?int $sales_tax;
    public string $credits_display_name;
    public ?string $recaptcha_version;
    public ?string $recaptcha_site_key;
    public ?string $recaptcha_secret_key;
    public ?string $phpmyadmin_url;
    public bool $alert_enabled;
    public string $alert_type;
    public ?string $alert_message;
    public string $theme;

    //public int $initial_user_role; wait for Roles & Permissions PR.

    public static function group(): string
    {
        return 'general';
    }



    /**
     * Summary of validations array
     * @return array<string, string>
     */
    public static function getValidations()
    {
        $themes = array_keys(self::getThemes());

        $validations = [
            'store_enabled' => 'nullable|string',
            'sales_tax' => 'nullable|numeric',
            'credits_display_name' => 'required|string',
            'recaptcha_version' => 'nullable|string|in:v2,v3',
            'recaptcha_site_key' => 'nullable|string',
            'recaptcha_secret_key' => 'nullable|string',
            'phpmyadmin_url' => 'nullable|string',
            'alert_enabled' => 'nullable|string',
            'alert_type' => 'required|in:primary,secondary,success,danger,warning,info',
            'alert_message' => 'nullable|string',
            'theme' => ['required', 'in:' . implode(',', $themes)],
        ];
        return $validations;
    }

    public static function getThemes()
    {
        $themes = array_diff(scandir(base_path('themes')), array('..', '.'));
        $themesWithLabels = [];
        foreach ($themes as $theme) {
            // Customize the label as needed. Example: "Blue_Infinity" => "Blue Infinity"
            $label = ucwords(str_replace(['_', '-'], ' ', $theme));
            $themesWithLabels[$theme] = $label;
        }

        return $themesWithLabels;
    }



    /**
     * Summary of optionTypes
     * Only used for the settings page
     * @return array<array<'type'|'label'|'description'|'options', string|bool|float|int|array<string, string>>>
     */
    public static function getOptionInputData()
    {
        $inputData = [
            'category_icon' => "fas fa-cog",
            'position' => 1,
            'store_enabled' => [
                'type' => 'boolean',
                'label' => 'Enable Store',
                'description' => 'Enable the store for users to purchase credits.'
            ],
            'sales_tax' => [
                'type' => 'number',
                'label' => 'Sales Tax in %',
                'description' => 'Your countrys sales tax in %'
            ],
            'credits_display_name' => [
                'type' => 'string',
                'label' => 'Credits Display Name',
                'description' => 'The name of the currency used.'
            ],
            'recaptcha_version' => [
                'type' => 'select',
                'label' => 'reCAPTCHA Version',
                'description' => 'Enable reCAPTCHA on the login page.',
                'options' => [
                    'v2' => 'Recaptcha V2',
                    'v3' => 'Recaptcha v3',
                    null => 'Disable',
                ],
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
                'type' => 'textarea',
                'label' => 'Alert Message',
                'description' => 'The message to display in the alert.'
            ],
        ];

        $inputData['theme'] = [
            'type' => 'select',
            'label' => 'Theme',
            'options' => self::getThemes(),
            'description' => 'The theme to use for the site.'
        ];


        return $inputData;
    }
}
