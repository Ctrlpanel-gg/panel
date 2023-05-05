<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class TicketSettings extends Settings
{
    public bool $enabled;

    public static function group(): string
    {
        return 'ticket';
    }

    /**
     * Summary of validations array
     * @return array<string, string>
     */
    public static function getValidations()
    {
        return [
            'enabled' => 'nullable|boolean',
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
            'category_icon' => 'fas fa-ticket-alt',
            'enabled' => [
                'label' => 'Enabled',
                'type' => 'boolean',
                'description' => 'Enable or disable the ticket system.',
            ],
        ];
    }
}
