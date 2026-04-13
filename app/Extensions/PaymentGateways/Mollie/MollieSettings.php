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
            'api_key' => [
                'type' => 'string',
                'label' => 'API Key',
                'description' => 'The API Key of your Mollie App',
            ],
            'webhook_secret' => [
                'type' => 'string',
                'label' => 'Webhook Secret',
                'description' => 'Secret token appended to webhook URLs to validate incoming requests.',
            ],
            'enabled' => [
                'type' => 'boolean',
                'label' => 'Enabled',
                'description' => 'Enable or disable this payment gateway',
            ],
        ];
    }
}
