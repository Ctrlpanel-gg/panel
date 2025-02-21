<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class TicketSettings extends Settings
{
    public bool $enabled = false;
    public ?string $information = null;

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
            'enabled' => 'nullable|string',
            'information' => 'nullable|string',
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
            'position' => 6,
            'enabled' => [
                'label' => 'Enabled',
                'type' => 'boolean',
                'description' => 'Enable or disable the ticket system.',
            ],
            'information' => [
                'label' => 'Ticket Information',
                'type' => 'textarea',
                'description' => 'Message shown on the right side when users create a new ticket.',
            ],
        ];
    }
}
