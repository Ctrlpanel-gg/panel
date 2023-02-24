<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateUserSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();
        
        // Get the user-set configuration values from the old table.
        $this->migrator->add('user.credits_reward_after_verify_discord', $table_exists ? $this->getOldValue('SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_DISCORD'): 250);
        $this->migrator->add('user.credits_reward_after_verify_email', $table_exists ? $this->getOldValue('SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_EMAIL'): 250);
        $this->migrator->add('user.force_discord_verification', $table_exists ? $this->getOldValue('SETTINGS::USER:FORCE_DISCORD_VERIFICATION'): false);
        $this->migrator->add('user.force_email_verification', $table_exists ? $this->getOldValue('SETTINGS::USER:FORCE_EMAIL_VERIFICATION'): false);
        $this->migrator->add('user.initial_credits', $table_exists ? $this->getOldValue('SETTINGS::USER:INITIAL_CREDITS'): 250);
        $this->migrator->add('user.initial_server_limit', $table_exists ? $this->getOldValue('SETTINGS::USER:INITIAL_SERVER_LIMIT'): 1);
        $this->migrator->add('user.min_credits_to_make_server', $table_exists ? $this->getOldValue('SETTINGS::USER:MINIMUM_REQUIRED_CREDITS_TO_MAKE_SERVER'): 50);
        $this->migrator->add('user.server_limit_after_irl_purchase', $table_exists ? $this->getOldValue('SETTINGS::USER:SERVER_LIMIT_AFTER_IRL_PURCHASE'): 10);
        $this->migrator->add('user.server_limit_after_verify_discord', $table_exists ? $this->getOldValue('SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_DISCORD'): 2);
        $this->migrator->add('user.server_limit_after_verify_email', $table_exists ? $this->getOldValue('SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_EMAIL'): 2);
        $this->migrator->add('user.register_ip_check', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:REGISTER_IP_CHECK"): true);
        $this->migrator->add('user.creation_enabled', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:CREATION_OF_NEW_USERS"): true);
    }

    public function getOldValue(string $key)
    {
        // Always get the first value of the key.
        $old_value = DB::table('settings_old')->where('key', '=', $key)->get(['value', 'type'])->first();

        // Handle the old values to return without it being a string in all cases.
        if ($old_value->type === "string" || $old_value->type === "text") {
            if (is_null($old_value->value)) {
                return '';
            }

            // Some values have the type string, but their values are boolean.
            if ($old_value->value === "false" || $old_value->value === "true") {
                return filter_var($old_value->value, FILTER_VALIDATE_BOOL);
            }

            return $old_value->value;
        }

        if ($old_value->type === "boolean") {
            return filter_var($old_value->value, FILTER_VALIDATE_BOOL);
        }

        return filter_var($old_value->value, FILTER_VALIDATE_INT);
    }
}