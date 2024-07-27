<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class MailTemplatesSettings extends Settings
{
    public ?string $mail_welcome_subject;
    public ?string $mail_welcome_body;

    public static function group(): string
    {
        return 'templates';
    }

    /**
     * Summary of validations array
     * @return array<string, string>
     */
    public static function getValidations()
    {
        return [
            'mail_welcome_subject' => 'nullable|string',
            'mail_welcome_body' => 'nullable|string',
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
            'category_icon' => 'fas fa-envelope',
            'mail_welcome_subject' => [
                'label' => 'Welcome Mail Subject',
                'type' => 'string',
                'description' => 'The subject of the welcome mail.',
            ],
            'mail_welcome_body' => [
                'label' => 'Welcome Mail Body',
                'type' => 'textarea',
                'description' => 'The body of the welcome mail.',
                'tooltip' => 'You can use {panel} and {user} as placeholders. These variables will be replaced with Panel name and User name respectively.',
            ],
        ];
    }
}
