<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class TicketSettings extends Settings
{
    public bool $enabled;
    public string $notify;

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
            'notify' => 'nullable|string',
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
            'notify' => [
                'label' => 'Notify',
                'type' => 'select',
                'description' => 'Who will receive an E-Mail when a new Ticket is created.',
                'options' => [
                    'admin' => 'Admins',
                    'moderator' => 'Moderators',
                    'all' => 'Admins and Moderators',
                    'none' => 'Nobody',
                ],
            ],
        ];
    }
}
