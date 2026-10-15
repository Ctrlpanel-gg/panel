<?php

namespace App\Extensions\PaymentGateways\Stripe;

use Spatie\LaravelSettings\Settings;

class StripeSettings extends Settings
{

    public bool $enabled = false;
    public ?string $publishable_key;
    public ?string $secret_key;
    public ?string $webhook_signing_secret;
    public ?string $test_publishable_key;
    public ?string $test_secret_key;
    public ?string $test_webhook_signing_secret;


    public static function group(): string
    {
        return 'stripe';
    }



    public static function getOptionInputData()
    {
        return [
            'category_icon' => 'fas fa-dollar-sign',
            'category_description' => 'Enable Stripe and configure your production and test credentials',
            'sections' => [
                'production' => [
                    'label' => 'Production',
                    'description' => 'Credentials used in production',
                ],
                'test' => [
                    'label' => 'Test',
                    'description' => 'Credentials used when app_env = local',
                ],
            ],
            'enabled' => [
                'type' => 'boolean',
                'label' => 'Enabled',
                'description' => 'Enable this payment gateway',
            ],
            'publishable_key' => [
                'type' => 'string',
                'label' => 'Publishable Key',
                'description' => 'The Publishable Key of your Stripe App',
                'section' => 'production',
            ],
            'secret_key' => [
                'type' => 'secret',
                'label' => 'Secret Key',
                'description' => 'The Secret Key of your Stripe App',
                'section' => 'production',
            ],
            'webhook_signing_secret' => [
                'type' => 'secret',
                'label' => 'Webhook Signing Secret',
                'description' => 'The Stripe webhook signing secret (whsec_...) for production endpoints',
                'section' => 'production',
            ],
            'test_publishable_key' => [
                'type' => 'string',
                'label' => 'Test Publishable Key',
                'description' => 'The Test Publishable Key used when app_env = local',
                'section' => 'test',
            ],
            'test_secret_key' => [
                'type' => 'secret',
                'label' => 'Test Secret Key',
                'description' => 'The Test Secret Key used when app_env = local',
                'section' => 'test',
            ],
            'test_webhook_signing_secret' => [
                'type' => 'secret',
                'label' => 'Test Webhook Signing Secret',
                'description' => 'The Stripe test webhook signing secret (whsec_...) used when app_env = local',
                'section' => 'test',
            ]
        ];
    }
}
