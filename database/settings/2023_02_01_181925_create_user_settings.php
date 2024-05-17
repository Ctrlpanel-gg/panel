<?php

use App\Classes\LegacySettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateUserSettings extends LegacySettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('user.credits_reward_after_verify_discord', $table_exists ? $this->getOldValue('SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_DISCORD', 250) : 250);
        $this->migrator->add('user.credits_reward_after_verify_email', $table_exists ? $this->getOldValue('SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_EMAIL', 250) : 250);
        $this->migrator->add('user.force_discord_verification', $table_exists ? $this->getOldValue('SETTINGS::USER:FORCE_DISCORD_VERIFICATION', false) : false);
        $this->migrator->add('user.force_email_verification', $table_exists ? $this->getOldValue('SETTINGS::USER:FORCE_EMAIL_VERIFICATION', false) : false);
        $this->migrator->add('user.initial_credits', $table_exists ? $this->getOldValue('SETTINGS::USER:INITIAL_CREDITS', 250) : 250);
        $this->migrator->add('user.initial_server_limit', $table_exists ? $this->getOldValue('SETTINGS::USER:INITIAL_SERVER_LIMIT', 1) : 1);
        $this->migrator->add('user.min_credits_to_make_server', $table_exists ? $this->getOldValue('SETTINGS::USER:MINIMUM_REQUIRED_CREDITS_TO_MAKE_SERVER', 50) : 50);
        $this->migrator->add('user.server_limit_after_irl_purchase', $table_exists ? $this->getOldValue('SETTINGS::USER:SERVER_LIMIT_AFTER_IRL_PURCHASE', 10) : 10);
        $this->migrator->add('user.server_limit_after_verify_discord', $table_exists ? $this->getOldValue('SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_DISCORD', 2) : 2);
        $this->migrator->add('user.server_limit_after_verify_email', $table_exists ? $this->getOldValue('SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_EMAIL', 2) : 2);
        $this->migrator->add('user.register_ip_check', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:REGISTER_IP_CHECK", true) : true);
        $this->migrator->add('user.creation_enabled', $table_exists ? $this->getOldValue("SETTINGS::SYSTEM:CREATION_OF_NEW_USERS", true) : true);
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_DISCORD',
                'value' => $this->getNewValue('credits_reward_after_verify_discord', 'user'),
                'type' => 'integer',
                'description' => 'The amount of credits that the user will receive after verifying their Discord account.',
            ],
            [
                'key' => 'SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_EMAIL',
                'value' => $this->getNewValue('credits_reward_after_verify_email', 'user'),
                'type' => 'integer',
                'description' => 'The amount of credits that the user will receive after verifying their email.',

            ],
            [
                'key' => 'SETTINGS::USER:FORCE_DISCORD_VERIFICATION',
                'value' => $this->getNewValue('force_discord_verification', 'user'),
                'type' => 'boolean',
                'description' => 'If the user must verify their Discord account to use the panel.',
            ],
            [
                'key' => 'SETTINGS::USER:FORCE_EMAIL_VERIFICATION',
                'value' => $this->getNewValue('force_email_verification', 'user'),
                'type' => 'boolean',
                'description' => 'If the user must verify their email to use the panel.',

            ],
            [
                'key' => 'SETTINGS::USER:INITIAL_CREDITS',
                'value' => $this->getNewValue('initial_credits', 'user'),
                'type' => 'integer',
                'description' => 'The amount of credits that the user will receive when they register.',
            ],
            [
                'key' => 'SETTINGS::USER:INITIAL_SERVER_LIMIT',
                'value' => $this->getNewValue('initial_server_limit', 'user'),
                'type' => 'integer',
                'description' => 'The amount of servers that the user will be able to create when they register.',
            ],
            [
                'key' => 'SETTINGS::USER:MINIMUM_REQUIRED_CREDITS_TO_MAKE_SERVER',
                'value' => $this->getNewValue('min_credits_to_make_server', 'user'),
                'type' => 'integer',
                'description' => 'The minimum amount of credits that the user must have to create a server.',
            ],
            [
                'key' => 'SETTINGS::USER:SERVER_LIMIT_AFTER_IRL_PURCHASE',
                'value' => $this->getNewValue('server_limit_after_irl_purchase', 'user'),
                'type' => 'integer',
                'description' => 'The amount of servers that the user will be able to create after making a real purchase.',
            ],
            [
                'key' => 'SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_DISCORD',
                'value' => $this->getNewValue('server_limit_after_verify_discord', 'user'),
                'type' => 'integer',
                'description' => 'The amount of servers that the user will be able to create after verifying their Discord account.',

            ],
            [
                'key' => 'SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_EMAIL',
                'value' => $this->getNewValue('server_limit_after_verify_email', 'user'),
                'type' => 'integer',
                'description' => 'The amount of servers that the user will be able to create after verifying their email.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:REGISTER_IP_CHECK',
                'value' => $this->getNewValue('register_ip_check', 'user'),
                'type' => 'boolean',
                'description' => 'If the user must verify their IP address to register.',
            ],
            [
                'key' => 'SETTINGS::SYSTEM:CREATION_OF_NEW_USERS',
                'value' => $this->getNewValue('creation_enabled', 'user'),
                'type' => 'boolean',
                'description' => 'If the user can register.',
            ],
        ]);

        try {
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
        } catch (Exception $e) {
            // Do nothing
        }
    }
}
