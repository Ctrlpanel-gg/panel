<?php

namespace App\Extensions\PaymentGateways\MercadoPago;

use Spatie\LaravelSettings\Settings;

class MercadoPagoSettings extends Settings
{

    public bool $enabled = false;
    public ?string $access_token;
    public ?string $webhook_secret;

    public static function group(): string
    {
        return 'mercadopago';
    }

    public static function encrypted(): array
    {
        return [
            'access_token',
            'webhook_secret',
        ];
    }

    public static function getOptionInputData()
    {
        return [
            'category_icon' => 'fas fa-dollar-sign',
            'category_description' => 'Enable Mercado Pago and enter your credentials',
            'sections' => [
                'credentials' => [
                    'label' => 'Credentials',
                    'description' => 'Your Mercado Pago credentials',
                ],
            ],
            'enabled' => [
                'type' => 'boolean',
                'label' => 'Enabled',
                'description' => 'Enable or disable this payment gateway',
            ],
            'access_token' => [
                'type' => 'secret',
                'label' => 'Access Token Key',
                'description' => 'The Access Token of your Mercado Pago App',
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
