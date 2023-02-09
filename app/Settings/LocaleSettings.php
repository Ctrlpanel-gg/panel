<?php

namespace App\Settings;

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

    /**
     * Summary of validations array
     * @return array<string, string>
     */
    public static function getValidations()
    {
        return [
            'available' => 'nullable|array',
            'clients_can_change' => 'nullable|string',
            'datatables' => 'nullable|string',
            'default' => 'nullable|string',
            'dynamic' => 'nullable|string',
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
            'available' => [
                'label' => 'Available Locales',
                'type' => 'multiselect',
                'description' => 'The locales that are available for the user to choose from.',
                'options' => config('app.available_locales'),
            ],
            'clients_can_change' => [
                'label' => 'Clients Can Change',
                'type' => 'boolean',
                'description' => 'Whether clients can change their locale.',
            ],
            'datatables' => [
                'label' => 'Datatables Locale',
                'type' => 'string',
                'description' => 'The datatables lang-code. <br><strong>Example:</strong> en-gb, fr_fr, de_de<br>More Information: <a href="https://datatables.net/plug-ins/i18n/">https://datatables.net/plug-ins/i18n/</a>',
            ],
            'default' => [
                'label' => 'Default Locale',
                'type' => 'select',
                'description' => 'The default locale to use.',
                'options' => config('app.available_locales'),
            ],
            'dynamic' => [
                'label' => 'Dynamic Locale',
                'type' => 'boolean',
                'description' => 'Whether to use the dynamic locale.',
            ],
        ];
    }
}
