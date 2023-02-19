<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class UserSettings extends Settings
{
    public float $credits_reward_after_verify_discord;

    public float $credits_reward_after_verify_email;

    public bool $force_discord_verification;

    public bool $force_email_verification;

    public float $initial_credits;

    public int $initial_server_limit;

    public float $min_credits_to_make_server;

    public int $server_limit_after_irl_purchase;

    public int $server_limit_after_verify_discord;

    public int $server_limit_after_verify_email;

    public bool $register_ip_check;

    public bool $creation_enabled;

    public static function group(): string
    {
        return 'user';
    }

    /**
     * Summary of optionTypes
     * Only used for the settings page
     * @return array<array<'type'|'label'|'description'|'options', string|boolean|number|array<string, string>>>
     */
    public static function getOptionInputData()
    {
        return [
            'credits_reward_after_verify_discord' => [
                'label' => 'Credits Reward After Verify Discord',
                'type' => 'number',
                'description' => 'The amount of credits a user gets after verifying their discord account.',
            ],
            'credits_reward_after_verify_email' => [
                'label' => 'Credits Reward After Verify Email',
                'type' => 'number',
                'description' => 'The amount of credits a user gets after verifying their email.',
            ],
            'force_discord_verification' => [
                'label' => 'Force Discord Verification',
                'type' => 'bool',
                'description' => 'Force users to verify their discord account.',
            ],
            'force_email_verification' => [
                'label' => 'Force Email Verification',
                'type' => 'bool',
                'description' => 'Force users to verify their email.',
            ],
            'initial_credits' => [
                'label' => 'Initial Credits',
                'type' => 'number',
                'description' => 'The amount of credits a user gets when they register.',
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
            ],
            'server_limit_after_irl_purchase' => [
                'label' => 'Server Limit After IRL Purchase',
                'type' => 'number',
                'description' => 'The amount of servers a user can create after they purchase a server.',
            ],
            'server_limit_after_verify_discord' => [
                'label' => 'Server Limit After Verify Discord',
                'type' => 'number',
                'description' => 'The amount of servers a user can create after they verify their discord account.',
            ],
            'server_limit_after_verify_email' => [
                'label' => 'Server Limit After Verify Email',
                'type' => 'number',
                'description' => 'The amount of servers a user can create after they verify their email.',
            ],
            'register_ip_check' => [
                'label' => 'Register IP Check',
                'type' => 'boolean',
                'description' => 'Check if the IP a user is registering from is already in use.',
            ],
            'creation_enabled' => [
                'label' => 'Creation Enabled',
                'type' => 'boolean',
                'description' => 'Whether or not users can create servers.',
            ],
        ];
    }
}
