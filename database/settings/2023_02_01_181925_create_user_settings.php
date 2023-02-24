<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateUserSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('user.credits_reward_after_verify_discord', $table_exists ? $this->getOldValue('SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_DISCORD') : 250);
        $this->migrator->add('user.credits_reward_after_verify_email', $table_exists ? $this->getOldValue('SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_EMAIL') : 250);
        $this->migrator->add('user.force_discord_verification', $table_exists ? $this->getOldValue('SETTINGS::USER:FORCE_DISCORD_VERIFICATION') : false);
        $this->migrator->add('user.force_email_verification', $table_exists ? $this->getOldValue('SETTINGS::USER:FORCE_EMAIL_VERIFICATION') : false);
        $this->migrator->add('user.initial_credits', $table_exists ? $this->getOldValue('SETTINGS::USER:INITIAL_CREDITS') : 250);
        $this->migrator->add('user.initial_server_limit', $table_exists ? $this->getOldValue('SETTINGS::USER:INITIAL_SERVER_LIMIT') : 1);
        $this->migrator->add('user.min_credits_to_make_server', $table_exists ? $this->getOldValue('SETTINGS::USER:MINIMUM_REQUIRED_CREDITS_TO_MAKE_SERVER') : 50);
        $this->migrator->add('user.server_limit_after_irl_purchase', $table_exists ? $this->getOldValue('SETTINGS::USER:SERVER_LIMIT_AFTER_IRL_PURCHASE') : 10);
        $this->migrator->add('user.server_limit_after_verify_discord', $table_exists ? $this->getOldValue('SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_DISCORD') : 2);
        $this->migrator->add('user.server_limit_after_verify_email', $table_exists ? $this->getOldValue('SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_EMAIL') : 2);
        $this->migrator->add('user.register_ip_check', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:REGISTER_IP_CHECK") : true);
        $this->migrator->add('user.creation_enabled', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:CREATION_OF_NEW_USERS") : true);
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_DISCORD',
                'value' => $this->getNewValue('credits_reward_after_verify_discord'),
                'type' => 'integer',
                'description' => 'The amount of credits that the user will receive after verifying their Discord account.',
            ],
            [
                'key' => 'SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_EMAIL',
                'value' => $this->getNewValue('credits_reward_after_verify_email'),
                'type' => 'integer',
                'description' => 'The amount of credits that the user will receive after verifying their email.',

            ],
            [
                'key' => 'SETTINGS::USER:FORCE_DISCORD_VERIFICATION',
                'value' => $this->getNewValue('force_discord_verification'),
                'type' => 'boolean',
                'description' => 'If the user must verify their Discord account to use the panel.',
            ],
            [
                'key' => 'SETTINGS::USER:FORCE_EMAIL_VERIFICATION',
                'value' => $this->getNewValue('force_email_verification'),
                'type' => 'boolean',
                'description' => 'If the user must verify their email to use the panel.',

            ],
            [
                'key' => 'SETTINGS::USER:INITIAL_CREDITS',
                'value' => $this->getNewValue('initial_credits'),
                'type' => 'integer',
                'description' => 'The amount of credits that the user will receive when they register.',
            ],
            [
                'key' => 'SETTINGS::USER:INITIAL_SERVER_LIMIT',
                'value' => $this->getNewValue('initial_server_limit'),
                'type' => 'integer',
                'description' => 'The amount of servers that the user will be able to create when they register.',
            ],
            [
                'key' => 'SETTINGS::USER:MINIMUM_REQUIRED_CREDITS_TO_MAKE_SERVER',
                'value' => $this->getNewValue('min_credits_to_make_server'),
                'type' => 'integer',
                'description' => 'The minimum amount of credits that the user must have to create a server.',
            ],
            [
                'key' => 'SETTINGS::USER:SERVER_LIMIT_AFTER_IRL_PURCHASE',
                'value' => $this->getNewValue('server_limit_after_irl_purchase'),
                'type' => 'integer',
                'description' => 'The amount of servers that the user will be able to create after making a real purchase.',
            ],
            [
                'key' => 'SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_DISCORD',
                'value' => $this->getNewValue('server_limit_after_verify_discord'),
                'type' => 'integer',
                'description' => 'The amount of servers that the user will be able to create after verifying their Discord account.',

            ],
            [
                'key' => 'SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_EMAIL',
                'value' => $this->getNewValue('server_limit_after_verify_email'),
                'type' => 'integer',
                'description' => 'The amount of servers that the user will be able to create after verifying their email.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:REGISTER_IP_CHECK',
                'value' => $this->getNewValue('register_ip_check'),
                'type' => 'boolean',
                'description' => 'If the user must verify their IP address to register.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:CREATION_OF_NEW_USERS',
                'value' => $this->getNewValue('creation_enabled'),
                'type' => 'boolean',
                'description' => 'If the user can register.',
            ],
        ]);

        $this->migrator->delete('user.credits_reward_after_verify_discord');
        $this->migrator->delete('user.credits_reward_after_verify_email');
        $this->migrator->delete('user.force_discord_verification');
        $this->migrator->delete('user.force_email_verification');
        $this->migrator->delete('user.initial_credits');
        $this->migrator->delete('user.initial_server_limit');
        $this->migrator->delete('user.min_credits_to_make_server');
        $this->migrator->delete('user.server_limit_after_irl_purchase');
        $this->migrator->delete('user.server_limit_after_verify_discord');
        $this->migrator->delete('user.server_limit_after_verify_email');
        $this->migrator->delete('user.register_ip_check');
        $this->migrator->delete('user.creation_enabled');
    }

    public function getNewValue(string $name)
    {
        $new_value = DB::table('settings')->where([['group', '=', 'user'], ['name', '=', $name]])->get(['payload'])->first();

        // Some keys returns '""' as a value.
        if ($new_value->payload === '""') {
            return null;
        }

        return $new_value->payload;
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
