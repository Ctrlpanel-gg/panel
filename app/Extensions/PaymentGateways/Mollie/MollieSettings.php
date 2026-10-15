<?php

namespace App\Extensions\PaymentGateways\Mollie;

use Spatie\LaravelSettings\Settings;

class MollieSettings extends Settings
{

    public bool $enabled = false;
    public ?string $api_key;
    public ?string $webhook_secret;

    public static function group(): string
    {
        return 'mollie';
    }



    public static function getOptionInputData()
    {
        return [
            'category_icon' => 'fas fa-dollar-sign',
            'category_description' => 'Enable Mollie and enter your credentials',
            'sections' => [
                'credentials' => [
                    'label' => 'Credentials',
                    'description' => 'Your Mollie credentials',
                ],
            ],
            'enabled' => [
                'type' => 'boolean',
                'label' => 'Enabled',
                'description' => 'Enable or disable this payment gateway',
            ],
            'api_key' => [
                'type' => 'secret',
                'label' => 'API Key',
                'description' => 'The API Key of your Mollie App',
                'section' => 'credentials',
            ],
            'webhook_secret' => [
                'type' => 'secret',
                'label' => 'Webhook Secret',
                'description' => 'Secret token appended to webhook URLs to validate incoming requests',
                'section' => 'credentials',
            ],
        ];
    }
}
