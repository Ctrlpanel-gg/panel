<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateUserSettings extends SettingsMigration
{
    public function up(): void
    {
        // Get the user-set configuration values from the old table.
        $this->migrator->add('user.credits_reward_after_verify_discord', ($this->getOldValue('SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_DISCORD') != null) ?: 250);
        $this->migrator->add('user.credits_reward_after_verify_email', ($this->getOldValue('SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_EMAIL') != null) ?: 250);
        $this->migrator->add('user.force_discord_verification', ($this->getOldValue('SETTINGS::USER:FORCE_DISCORD_VERIFICATION') != null) ?: false);
        $this->migrator->add('user.force_email_verification', ($this->getOldValue('SETTINGS::USER:FORCE_EMAIL_VERIFICATION') != null) ?: false);
        $this->migrator->add('user.initial_credits', ($this->getOldValue('SETTINGS::USER:INITIAL_CREDITS') != null) ?: 250);
        $this->migrator->add('user.initial_server_limit', ($this->getOldValue('SETTINGS::USER:INITIAL_SERVER_LIMIT') != null) ?: 1);
        $this->migrator->add('user.min_credits_to_make_server', ($this->getOldValue('SETTINGS::USER:MINIMUM_REQUIRED_CREDITS_TO_MAKE_SERVER') != null) ?: 50);
        $this->migrator->add('user.server_limit_after_irl_purchase', ($this->getOldValue('SETTINGS::USER:SERVER_LIMIT_AFTER_IRL_PURCHASE') != null) ?: 10);
        $this->migrator->add('user.server_limit_after_verify_discord', ($this->getOldValue('SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_DISCORD') != null) ?: 2);
        $this->migrator->add('user.server_limit_after_verify_email', ($this->getOldValue('SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_EMAIL') != null) ?: 2);
        $this->migrator->add('user.register_ip_check', ($this->getOldValue("SETTINGS::SYSTEM:REGISTER_IP_CHECK") != null) ?: true);
        $this->migrator->add('user.creation_enabled', ($this->getOldValue("SETTINGS::SYSTEM:CREATION_OF_NEW_USERS") != null) ?: true);
    }

    public function getOldValue(string $key)
    {
        if (DB::table('settings_old')->exists()) {
            return DB::table('settings_old')->where('key', '=', $key)->get(['value']);
        }

        return null;
    }
}