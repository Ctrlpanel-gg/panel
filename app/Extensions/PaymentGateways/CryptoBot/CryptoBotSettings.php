<?php

namespace App\Extensions\PaymentGateways\CryptoBot;

use Spatie\LaravelSettings\Settings;

class CryptoBotSettings extends Settings
{

    public bool $enabled = false;
    public ?string $api_key;

    public static function group(): string
    {
        return 'cryptobot';
    }



    public static function getOptionInputData()
    {
        return [
            'category_icon' => 'fas fa-dollar-sign',
            'api_key' => [
                'type' => 'string',
                'label' => 'API Key',
                'description' => 'The API Key of your CryptoPay app',
            ],
            'hidden_message' => [
                'type' => 'string',
                'label' => 'Hidden Message',
                'description' => 'Message displayed after payment',
            ],
            'enabled' => [
                'type' => 'boolean',
                'label' => 'Enabled',
                'description' => 'Enable or disable this payment gateway',
            ],
        ];
    }
}
