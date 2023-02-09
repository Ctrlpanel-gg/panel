<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class InvoiceSettings extends Settings
{
    public string $company_address;

    public string $company_mail;

    public string $company_name;

    public string $company_phone;

    public int $company_vat;

    public string $company_website;

    public bool $enabled;

    public string $prefix;

    public static function group(): string
    {
        return 'invoice';
    }

    /**
     * Summary of optionTypes
     * Only used for the settings page
     * @return array<array<'type'|'label'|'description'|'options', string|bool|float|int|array<string, string>>>
     */
    public static function getOptionInputData()
    {
        return [
            'company_address' => [
                'label' => 'Company Address',
                'type' => 'string',
                'description' => 'The address of your company.',
            ],
            'company_mail' => [
                'label' => 'Company Mail',
                'type' => 'string',
                'description' => 'The mail of your company.',
            ],
            'company_name' => [
                'label' => 'Company Name',
                'type' => 'string',
                'description' => 'The name of your company.',
            ],
            'company_phone' => [
                'label' => 'Company Phone',
                'type' => 'string',
                'description' => 'The phone of your company.',
            ],
            'company_vat' => [
                'label' => 'Company VAT',
                'type' => 'string',
                'description' => 'The VAT of your company.',
            ],
            'company_website' => [
                'label' => 'Company Website',
                'type' => 'string',
                'description' => 'The website of your company.',
            ],
            'enabled' => [
                'label' => 'Enabled',
                'type' => 'boolean',
                'description' => 'Enable or disable invoices.',
            ],
            'prefix' => [
                'label' => 'Prefix',
                'type' => 'string',
                'description' => 'The prefix of your invoices.',
            ],
        ];
    }
}
