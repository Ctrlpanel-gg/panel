<?php

namespace App\Extensions\PaymentGateways\PayPal;

use Spatie\LaravelSettings\Settings;

class PayPalSettings extends Settings
{
    public bool $enabled = false;
    public ?string $client_id;
    public ?string $client_secret;
    public ?string $webhook_id;
    public ?string $sandbox_client_id;
    public ?string $sandbox_client_secret;
    public ?string $sandbox_webhook_id;

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
            'category_description' => 'Enable PayPal and configure your production and sandbox credentials',
            'sections' => [
                'production' => [
                    'label' => 'Production',
                    'description' => 'Credentials used in production',
                ],
                'sandbox' => [
                    'label' => 'Sandbox',
                    'description' => 'Credentials used when app_env = local',
                ],
            ],
            'enabled' => [
                'type' => 'boolean',
                'label' => 'Enabled',
                'description' => 'Enable this payment gateway',
            ],
            'client_id' => [
                'type' => 'string',
                'label' => 'Client ID',
                'description' => 'The Client ID of your PayPal App',
                'section' => 'production',
            ],
            'client_secret' => [
                'type' => 'secret',
                'label' => 'Client Secret',
                'description' => 'The Client Secret of your PayPal App',
                'section' => 'production',
            ],
            'webhook_id' => [
                'type' => 'string',
                'label' => 'Webhook ID',
                'description' => 'PayPal webhook ID used to verify production webhook signatures',
                'section' => 'production',
            ],
            'sandbox_client_id' => [
                'type' => 'string',
                'label' => 'Sandbox Client ID',
                'description' => 'The Sandbox Client ID  used when app_env = local',
                'section' => 'sandbox',
            ],
            'sandbox_client_secret' => [
                'type' => 'secret',
                'label' => 'Sandbox Client Secret',
                'description' => 'The Sandbox Client Secret  used when app_env = local',
                'section' => 'sandbox',
            ],
            'sandbox_webhook_id' => [
                'type' => 'string',
                'label' => 'Sandbox Webhook ID',
                'description' => 'PayPal webhook ID used to verify webhook signatures when app_env = local',
                'section' => 'sandbox',
            ],
        ];
    }
}
