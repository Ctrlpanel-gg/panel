<?php

namespace App\Extensions\PaymentGateways\PayPal;

use Spatie\LaravelSettings\Settings;

class PayPalSettings extends Settings
{
    public bool $enabled = false;
    public ?string $client_id;
    public ?string $client_secret;
    public ?string $sandbox_client_id;
    public ?string $sandbox_client_secret;

    public static function group(): string
    {
        return 'paypal';
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
                'type' => 'string',
                'label' => 'Client ID',
                'description' => 'The Client ID of your PayPal App',
            ],
            'client_secret' => [
                'type' => 'string',
                'label' => 'Client Secret',
                'description' => 'The Client Secret of your PayPal App',
            ],
            'enabled' => [
                'type' => 'boolean',
                'label' => 'Enabled',
                'description' => 'Enable this payment gateway',
            ],
            'sandbox_client_id' => [
                'type' => 'string',
                'label' => 'Sandbox Client ID',
                'description' => 'The Sandbox Client ID  used when app_env = local',
            ],
            'sandbox_client_secret' => [
                'type' => 'string',
                'label' => 'Sandbox Client Secret',
                'description' => 'The Sandbox Client Secret  used when app_env = local',
            ],
        ];
    }
}
