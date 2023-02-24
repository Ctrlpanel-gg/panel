<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ReferralSettings extends Settings
{
    public string $allowed;

    public bool $always_give_commission;

    public bool $enabled;

    public float $reward;

    public string $mode;

    public int $percentage;

    public static function group(): string
    {
        return 'referral';
    }

    /**
     * Summary of validations array
     * @return array<string, string>
     */
    public static function getValidations()
    {
        return [
            'allowed' => 'nullable|string',
            'always_give_commission' => 'nullable|boolean',
            'enabled' => 'nullable|boolean',
            'reward' => 'nullable|numeric',
            'mode' => 'nullable|string',
            'percentage' => 'nullable|numeric',
        ];
    }

    /**
     * Summary of optionTypes
     * Only used for the settings page
     * @return array<array<'type'|'label'|'description'|'options', string|bool|float|int|array<string, string>>>
     */
    public static function getOptionInputData()
    {
        return [
            'allowed' => [
                'label' => 'Allowed',
                'type' => 'select',
                'description' => 'Who is allowed to see their referral-URL',
                'options' => [
                    'everyone' => 'Everyone',
                    'clients' => 'Clients',
                ],
            ],
            'always_give_commission' => [
                'label' => 'Always Give Commission',
                'type' => 'boolean',
                'description' => 'Always give commission to the referrer.',
            ],
            'enabled' => [
                'label' => 'Enabled',
                'type' => 'boolean',
                'description' => 'Enable referral system.',
            ],
            'reward' => [
                'label' => 'Reward',
                'type' => 'number',
                'description' => 'Reward for the referrer.',
            ],
            'mode' => [
                'label' => 'Mode',
                'type' => 'select',
                'description' => 'Referral mode.',
                'options' => [
                    'comission' => 'Comission',
                    'sign-up' => 'Sign-Up',
                    'both' => 'Both',
                ],
            ],
            'percentage' => [
                'label' => 'Percentage',
                'type' => 'number',
                'description' => 'If a referred user buys credits, the referral-user will get x% of the Credits the referred user bought.',
            ],
        ];
    }
}
