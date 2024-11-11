<?php

namespace App\Extensions\PaymentGateways\Mollie;

use Spatie\LaravelSettings\Settings;

class MollieSettings extends Settings
{

    public bool $enabled = false;
    public ?string $api_key;

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
            'enabled' => [
                'type' => 'boolean',
                'label' => 'Enabled',
                'description' => 'Enable or disable this payment gateway',
            ],
        ];
    }
}
