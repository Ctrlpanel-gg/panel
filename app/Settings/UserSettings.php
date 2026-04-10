<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class UserSettings extends Settings
{
    public bool $creation_enabled = true;
    public bool $register_ip_check = false;
    public bool $force_email_verification = false;
    public bool $force_discord_verification = false;
    public int $credits_reward_after_verify_email = 0;
    public int $credits_reward_after_verify_discord = 0;
    public int $initial_credits = 250000;
    public int $initial_server_limit = 1;
    public int $server_limit_increment_after_verify_email = 0;
    public int $server_limit_increment_after_verify_discord = 0;
    public int $server_limit_increment_after_irl_purchase = 0;

    public static function group(): string
    {
        return 'user';
    }

    /**
     * Summary of validations array
     * @return array<string, string>
     */
    public static function getValidations()
    {
        return [
            'creation_enabled' => 'nullable|string',
            'register_ip_check' => 'nullable|string',
            'force_email_verification' => 'nullable|string',
            'force_discord_verification' => 'nullable|string',
            'credits_reward_after_verify_email' => 'required|numeric',
            'credits_reward_after_verify_discord' => 'required|numeric',
            'initial_credits' => 'required|numeric',
            'initial_server_limit' => 'required|numeric',
            'server_limit_increment_after_verify_email' => 'required|numeric',
            'server_limit_increment_after_verify_discord' => 'required|numeric',
            'server_limit_increment_after_irl_purchase' => 'required|numeric',
        ];
    }

    /**
     * Summary of optionTypes
     * Only used for the settings page
     * @return array<array<'type'|'label'|'description'|'options', string|boolean|number|array<string, string>>>
     */
    public static function getOptionInputData()
    {
        return [
            'category_icon' => 'fas fa-user',
            'position' => 7,
            'creation_enabled' => [
                'label' => 'Creation Enabled',
                'type' => 'boolean',
                'description' => 'Enable the user registration.',
            ],
            'register_ip_check' => [
                'label' => 'Register IP Check Enabled',
                'type' => 'boolean',
                'description' => 'Check if the IP a user is registering from is already in use.',
            ],
            'force_email_verification' => [
                'label' => 'Force Email Verification',
                'type' => 'boolean',
                'description' => 'Force users to verify their email.',
            ],
            'force_discord_verification' => [
                'label' => 'Force Discord Verification',
                'type' => 'boolean',
                'description' => 'Force users to verify their discord account.',
            ],
            'credits_reward_after_verify_email' => [
                'label' => 'Credits Reward After Verify Email',
                'type' => 'number',
                'description' => 'The amount of credits a user gets after verifying their email.',
                'mustBeConverted' => true,
            ],
            'credits_reward_after_verify_discord' => [
                'label' => 'Credits Reward After Verify Discord',
                'type' => 'number',
                'description' => 'The amount of credits a user gets after verifying their discord account.',
                'mustBeConverted' => true,
            ],
            'initial_credits' => [
                'label' => 'Initial Credits',
                'type' => 'number',
                'description' => 'The amount of credits a user gets when they register.',
                'mustBeConverted' => true,
            ],
            'initial_server_limit' => [
                'label' => 'Initial Server Limit',
                'type' => 'number',
                'description' => 'The amount of servers a user can create when they register.',
            ],
            'server_limit_increment_after_verify_email' => [
                'label' => 'Server Limit Increase After Verify Email',
                'type' => 'number',
                'description' => 'Specifies how many additional servers a user can create after verifying their email address.',
            ],
            'server_limit_increment_after_verify_discord' => [
                'label' => 'Server Limit Increase After Verify Discord',
                'type' => 'number',
                'description' => 'Specifies how many additional servers a user can create after verifying their Discord account.',
            ],
            'server_limit_increment_after_irl_purchase' => [
                'label' => 'Server Limit Increase After first purchase',
                'type' => 'number',
                'description' => 'Specifies how many additional servers a user can create after making their first purchase.',
            ],
        ];
    }
}
