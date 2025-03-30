<?php

use App\Models\Settings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $credits_reward_after_verify_discord = Settings::get('user.credits_reward_after_verify_discord');
        $credits_reward_after_verify_email = Settings::get('user.credits_reward_after_verify_email');
        $initial_credits = Settings::get('user.initial_credits');
        $min_credits_to_make_server = Settings::get('user.min_credits_to_make_server');

        if ($credits_reward_after_verify_discord) {
            Settings::set('user.credits_reward_after_verify_discord', $credits_reward_after_verify_discord * 1000);
        }

        if ($credits_reward_after_verify_email) {
            Settings::set('user.credits_reward_after_verify_email', $credits_reward_after_verify_email * 1000);
        }

        if ($initial_credits) {
            Settings::set('user.initial_credits', $initial_credits * 1000);
        }

        if ($min_credits_to_make_server) {
            Settings::set('user.min_credits_to_make_server', $min_credits_to_make_server * 1000);
        }
    }

    public function down(): void
    {
        $credits_reward_after_verify_discord = Settings::get('user.credits_reward_after_verify_discord');
        $credits_reward_after_verify_email = Settings::get('user.credits_reward_after_verify_email');
        $initial_credits = Settings::get('user.initial_credits');
        $min_credits_to_make_server = Settings::get('user.min_credits_to_make_server');

        if ($credits_reward_after_verify_discord) {
            Settings::set('user.credits_reward_after_verify_discord', $credits_reward_after_verify_discord / 1000);
        }

        if ($credits_reward_after_verify_email) {
            Settings::set('user.credits_reward_after_verify_email', $credits_reward_after_verify_email / 1000);
        }

        if ($initial_credits) {
            Settings::set('user.initial_credits', $initial_credits / 1000);
        }

        if ($min_credits_to_make_server) {
            Settings::set('user.min_credits_to_make_server', $min_credits_to_make_server / 1000);
        }
    }
};
