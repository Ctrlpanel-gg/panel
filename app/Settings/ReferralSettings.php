<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ReferralSettings extends Settings
{
    public bool $always_give_commission;
    public bool $enabled;
    public ?float $reward;
    public string $mode;
    public ?int $percentage;

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
            'always_give_commission' => 'nullable|string',
            'enabled' => 'nullable|string',
            'reward' => 'nullable|numeric',
            'mode' => 'required|in:commission,sign-up,both',
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
            'category_icon' => 'fas fa-user-friends',
            'position' => 8,
            'always_give_commission' => [
                'label' => 'Always Give Commission',
                'type' => 'boolean',
                'description' => 'Always give commission to the referrer or only on the first Purchase.',
            ],
            'enabled' => [
                'label' => 'Enabled',
                'type' => 'boolean',
                'description' => 'Enable referral system.',
            ],
            'reward' => [
                'label' => 'Reward',
                'type' => 'number',
                'step' => '0.01',
                'description' => 'Reward in credits for the referrer.',
            ],
            'mode' => [
                'label' => 'Mode',
                'type' => 'select',
                'description' => 'Referral mode.',
                'options' => [
                    'commission' => 'Commission',
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
