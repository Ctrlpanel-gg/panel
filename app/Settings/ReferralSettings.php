<?php

namespace App\Settings;

use App\Models\User;
use Spatie\LaravelSettings\Settings;

class ReferralSettings extends Settings
{
    public bool $enabled = false;
    public bool $always_give_commission = false;
    public string $mode = 'commission';
    public ?int $reward = null;
    public ?int $percentage = null;

    public bool $require_email_verification = false;

    /**
     * Determine whether the sign-up reward should be granted for a user.
     */
    public function rewardsOnSignUp(User $user): bool
    {
        return in_array($this->mode, ['sign-up', 'both']) &&
            (!$this->require_email_verification || $user->hasVerifiedEmail());
    }

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
            'enabled' => 'nullable|string',
            'always_give_commission' => 'nullable|string',
            'mode' => 'required|in:commission,sign-up,both',
            'reward' => 'nullable|numeric',
            'percentage' => 'nullable|numeric',
            'require_email_verification' => 'nullable|string',
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
            'category_description' => 'Configure the referral system and what referrers earn',
            'enabled' => [
                'label' => 'Enabled',
                'type' => 'boolean',
                'description' => 'Enable referral system',
            ],
            'always_give_commission' => [
                'label' => 'Always Give Commission',
                'type' => 'boolean',
                'description' => 'Always give commission to the referrer or only on the first Purchase',
            ],
            'mode' => [
                'label' => 'Mode',
                'type' => 'select',
                'description' => 'Referral mode',
                'options' => [
                    'sign-up' => 'Sign-Up',
                    'commission' => 'Commission',
                    'both' => 'Both',
                ],
            ],
            'reward' => [
                'label' => 'Sign-Up Reward',
                'type' => 'number',
                'step' => '0.001',
                'description' => 'Reward in credits for the referrer',
                'mustBeConverted' => true,
            ],
            'percentage' => [
                'label' => 'Commission Percentage',
                'type' => 'number',
                'description' => 'Percentage of credits earned from purchases by referred users',
            ],
            'require_email_verification' => [
                'label' => 'Require Email Verification',
                'type' => 'boolean',
                'description' => 'Require referred users to verify their email before the referral reward is granted.',
            ],
        ];
    }
}
