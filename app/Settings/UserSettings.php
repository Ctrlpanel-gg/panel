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
}