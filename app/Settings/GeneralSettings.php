<?php

namespace App\Settings;

use App\Helpers\CurrencyHelper;
use Spatie\LaravelSettings\Settings;

class GeneralSettings extends Settings
{
    public string $credits_display_name = 'Credits';
    public ?string $currency_format_override = null;
    public bool $store_enabled = false;
    public ?float $sales_tax = null;
    public ?string $recaptcha_version = null;
    public ?string $recaptcha_site_key = null;
    public ?string $recaptcha_secret_key = null;
    public ?string $phpmyadmin_url = null;
    public string $theme = 'default';
    public bool $alert_enabled = false;
    public string $alert_type = 'info';
    public ?string $alert_message = null;

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
            'currency_format_override' => 'nullable|string|in:' . implode(',', array_merge([''], config('app.available_locales'))),
            'recaptcha_version' => 'nullable|string|in:v2,v3,turnstile',
            'recaptcha_site_key' => 'nullable|string',
            'recaptcha_secret_key' => 'nullable|string',
            'phpmyadmin_url' => 'nullable|string',
            'theme' => ['required', 'in:' . implode(',', $themes)],
            'alert_enabled' => 'nullable|string',
            'alert_type' => 'required|in:primary,secondary,success,danger,warning,info',
            'alert_message' => 'nullable|string',
        ];
        return $validations;
    }

    public static function getCurrencyFormatOptions()
    {
        $options = [];
        $locales = config('app.available_locales');
        $helper = app(CurrencyHelper::class);

        foreach ($locales as $locale) {
            $sample = $helper->formatForDisplay(1234560, 2, $locale, true);
            $options[$locale] = "$locale: $sample";
        }

        return $options;
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
            'category_description' => 'Core settings for your panel, including the store, theme, security and global alerts',
            'sections' => [
                'store' => [
                    'label' => 'Store',
                    'description' => 'Settings for the store and currency',
                ],
                'theme' => [
                    'label' => 'Theme',
                    'description' => 'The look of your panel',
                ],
                'recaptcha' => [
                    'label' => 'Captcha',
                    'description' => 'Protect the login page against bots',
                ],
                'phpmyadmin' => [
                    'label' => 'phpMyAdmin',
                    'description' => 'Link to your phpMyAdmin installation',
                ],
                'alert' => [
                    'label' => 'Alert',
                    'description' => 'Display a global alert on the home page',
                ],
            ],
            'credits_display_name' => [
                'type' => 'string',
                'label' => 'Credits Display Name',
                'description' => 'The name of the currency used',
            ],
            'currency_format_override' => [
                'type' => 'select',
                'label' => 'Currency Format Override',
                'description' => 'Force all currency displays to use this locale\'s formatting, overriding the current locale',
                'options' => array_merge(['' => 'Auto (Use Current Locale)'], self::getCurrencyFormatOptions()),
                'identifier' => 'value',
            ],
            'store_enabled' => [
                'type' => 'boolean',
                'label' => 'Enable Store',
                'description' => 'Enable the store for users to purchase credits',
                'section' => 'store',
            ],
            'sales_tax' => [
                'type' => 'number',
                'label' => 'Sales Tax in %',
                'description' => 'Your countrys sales tax in %',
                'step' => '0.01',
                'section' => 'store',
            ],
            'recaptcha_version' => [
                'type' => 'select',
                'label' => 'Captcha Version',
                'description' => 'Enable a captcha on the login page',
                'options' => [
                    'v2' => 'Google reCAPTCHA V2',
                    'v3' => 'Google reCAPTCHA v3',
                    'turnstile' => 'Cloudflare Turnstile',
                    null => 'Disable',
                ],
                'section' => 'recaptcha',
            ],
            'recaptcha_site_key' => [
                'type' => 'string',
                'label' => 'Captcha Site Key',
                'description' => 'The site key for the captcha',
                'section' => 'recaptcha',
            ],
            'recaptcha_secret_key' => [
                'type' => 'secret',
                'label' => 'Captcha Secret Key',
                'description' => 'The secret key for the captcha',
                'section' => 'recaptcha',
            ],
            'phpmyadmin_url' => [
                'type' => 'string',
                'label' => 'phpMyAdmin URL',
                'description' => 'The URL of your phpMyAdmin installation',
                'section' => 'phpmyadmin',
            ],
            'alert_enabled' => [
                'type' => 'boolean',
                'label' => 'Enable Alert',
                'description' => 'Enable an alert to be displayed on the home page',
                'section' => 'alert',
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
                'description' => 'The type of alert to display',
                'section' => 'alert',
            ],
            'alert_message' => [
                'type' => 'textarea',
                'label' => 'Alert Message',
                'description' => 'The message to display in the alert',
                'section' => 'alert',
            ],
        ];

        $inputData['theme'] = [
            'type' => 'select',
            'label' => 'Theme',
            'options' => self::getThemes(),
            'description' => 'The theme to use for the site',
            'section' => 'theme',
        ];


        return $inputData;
    }
}
