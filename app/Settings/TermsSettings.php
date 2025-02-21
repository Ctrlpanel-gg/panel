<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class TermsSettings extends Settings
{
    public ?string $terms_of_service = null;
    public ?string $privacy_policy = null;
    public ?string $imprint = null;

    public static function group(): string
    {
        return 'terms';
    }

    /**
     * Summary of validations array
     * @return array<string, string>
     */
    public static function getValidations()
    {
        return [
            'terms_of_service' => 'nullable|string',
            'privacy_policy' => 'nullable|string',
            'imprint' => 'nullable|string'
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
            'category_icon' => 'fas fa-file-signature',
            'position' => 1,
            'terms_of_service' => [
                'label' => 'Terms of Service',
                'type' => 'textarea',
                'description' => 'Terms of Service shown to users.',
            ],
            'privacy_policy' => [
                'label' => 'Privacy Policy',
                'type' => 'textarea',
                'description' => 'Privacy Policy shown to users.',
            ],
            'imprint' => [
                'label' => 'Imprint',
                'type' => 'textarea',
                'description' => 'Imprint shown to users.',
            ],
        ];
    }
}
