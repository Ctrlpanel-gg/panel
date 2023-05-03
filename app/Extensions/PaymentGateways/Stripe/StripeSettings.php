<?php

namespace App\Extensions\PaymentGateways\Stripe;

use Spatie\LaravelSettings\Settings;

class StripeSettings extends Settings
{

    public bool $enabled = false;
    public ?string $secret_key;
    public ?string $endpoint_secret;
    public ?string $test_secret_key;
    public ?string $test_endpoint_secret;


    public static function group(): string
    {
        return 'stripe';
    }



    public static function getOptionInputData()
    {
        return [
            'category_icon' => 'fas fa-dollar-sign',
            'secret_key' => [
                'type' => 'string',
                'label' => 'Secret Key',
                'description' => 'The Secret Key of your Stripe App',
            ],
            'endpoint_secret' => [
                'type' => 'string',
                'label' => 'Endpoint Secret',
                'description' => 'The Endpoint Secret of your Stripe App',
            ],
            'test_secret_key' => [
                'type' => 'string',
                'label' => 'Test Secret Key',
                'description' => 'The Test Secret Key used when app_env = local',
            ],
            'test_endpoint_secret' => [
                'type' => 'string',
                'label' => 'Test Endpoint Secret',
                'description' => 'The Test Endpoint Secret used when app_env = local',
            ],
            'enabled' => [
                'type' => 'boolean',
                'label' => 'Enabled',
                'description' => 'Enable this payment gateway',
            ]
        ];
    }
}
