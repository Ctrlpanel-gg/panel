<?php

namespace App\Extensions\PaymentGateways\PayPal;

use Spatie\LaravelSettings\Settings;

class PayPalSettings extends Settings
{
    public ?string $client_id;
    public ?string $client_secret;


    public static function group(): string
    {
        return 'paypal';
    }


    public static function encrypted(): array
    {
        return [
            'client_id',
            'client_secret'
        ];
    }

    /**
     * Summary of optionInputData array
     * Only used for the settings page
     * @return array<array<'type'|'label'|'description'|'options', string|bool|float|int|array<string, string>>>
     */
    public static function getOptionInputData()
    {
        return [
            'category_icon' => 'fas fa-dollar-sign',
            'client_id' => [
                'type' => 'text',
                'label' => 'Client ID',
                'description' => 'The Client ID of your PayPal App',
            ],
            'client_secret' => [
                'type' => 'text',
                'label' => 'Client Secret',
                'description' => 'The Client Secret of your PayPal App',
            ]
        ];
    }
}
