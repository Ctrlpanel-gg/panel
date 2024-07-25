<?php

namespace App\Extensions\PaymentGateways\MercadoPago;

use Spatie\LaravelSettings\Settings;

class MercadoPagoSettings extends Settings
{

    public bool $enabled = false;
    public ?string $access_token;

    public static function group(): string
    {
        return 'mercadopago';
    }

    public static function encrypted(): array
    {
        return [
            'access_token'
        ];
    }

    public static function getOptionInputData()
    {
        return [
            'category_icon' => 'fas fa-dollar-sign',
            'access_token' => [
                'type' => 'string',
                'label' => 'Access Token Key',
                'description' => 'The Access Token of your Mercado Pago App',
            ],
            'enabled' => [
                'type' => 'boolean',
                'label' => 'Enabled',
                'description' => 'Enable or disable this payment gateway',
            ],
        ];
    }
}
