<?php

namespace App\Settings;

use App\Casts\Settings\CurrencyCast;
use Spatie\LaravelSettings\Settings;

class UserSettings extends Settings
{
    public bool $register_ip_check = false;
    public bool $creation_enabled = true;
    public int $credits_reward_after_verify_discord = 0;
    public int $credits_reward_after_verify_email = 0;
    public bool $force_discord_verification = false;
    public bool $force_email_verification = false;
    public int $initial_credits = 250000;
    public int $initial_server_limit = 1;
    public int $min_credits_to_make_server = 0;
    public int $server_limit_increment_after_irl_purchase = 0;
    public int $server_limit_increment_after_verify_discord = 0;
    public int $server_limit_increment_after_verify_email = 0;

    public static function group(): string
    {
        return 'user';
    }

    /**
     * Casts the settings to the correct type.
     *
     * @return array<string, CurrencyCast>
     */
    public static function casts(): array
    {
        return [
            'credits_reward_after_verify_discord' => CurrencyCast::class,
            'credits_reward_after_verify_email' => CurrencyCast::class,
            'initial_credits' => CurrencyCast::class,
            'min_credits_to_make_server' => CurrencyCast::class,
        ];
    }

    /**
     * Summary of validations array
     * @return array<string, string>
     */
    public static function getValidations()
    {
        return [
            'credits_reward_after_verify_discord' => 'required|numeric',
            'credits_reward_after_verify_email' => 'required|numeric',
            'force_discord_verification' => 'nullable|string',
            'force_email_verification' => 'nullable|string',
            'initial_credits' => 'required|numeric',
            'initial_server_limit' => 'required|numeric',
            'min_credits_to_make_server' => 'required|numeric',
            'server_limit_increment_after_irl_purchase' => 'required|numeric',
            'server_limit_increment_after_verify_discord' => 'required|numeric',
            'server_limit_increment_after_verify_email' => 'required|numeric',
            'register_ip_check' => 'nullable|string',
            'creation_enabled' => 'nullable|string',
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
            'credits_reward_after_verify_discord' => [
                'label' => 'Credits Reward After Verify Discord',
                'type' => 'number',
                'description' => 'The amount of credits a user gets after verifying their discord account.',
                'mustBeConverted' => true,
            ],
            'credits_reward_after_verify_email' => [
                'label' => 'Credits Reward After Verify Email',
                'type' => 'number',
                'description' => 'The amount of credits a user gets after verifying their email.',
                'mustBeConverted' => true,
            ],
            'force_discord_verification' => [
                'label' => 'Force Discord Verification',
                'type' => 'boolean',
                'description' => 'Force users to verify their discord account.',
            ],
            'force_email_verification' => [
                'label' => 'Force Email Verification',
                'type' => 'boolean',
                'description' => 'Force users to verify their email.',
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
            'min_credits_to_make_server' => [
                'label' => 'Min Credits To Make Server',
                'type' => 'number',
                'description' => 'The minimum amount of credits a user needs to create a server.',
                'mustBeConverted' => true,
            ],
            'server_limit_increment_after_irl_purchase' => [
                'label' => 'Server Limit Increase After first purchase',
                'type' => 'number',
                'description' => 'Specifies how many additional servers a user can create after making their first purchase.',
            ],
            'server_limit_increment_after_verify_discord' => [
                'label' => 'Server Limit Increase After Verify Discord',
                'type' => 'number',
                'description' => 'Specifies how many additional servers a user can create after verifying their Discord account.',
            ],
            'server_limit_increment_after_verify_email' => [
                'label' => 'Server Limit Increase After Verify Email',
                'type' => 'number',
                'description' => 'Specifies how many additional servers a user can create after verifying their email address.',
            ],
            'register_ip_check' => [
                'label' => 'Register IP Check Enabled',
                'type' => 'boolean',
                'description' => 'Check if the IP a user is registering from is already in use.',
            ],
            'creation_enabled' => [
                'label' => 'Creation Enabled',
                'type' => 'boolean',
                'description' => 'Enable the user registration.',
            ],
        ];
    }
}
