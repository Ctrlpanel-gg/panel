<?php

use App\Classes\LegacySettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateReferralSettings extends LegacySettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('referral.always_give_commission', $table_exists ? $this->getOldValue('SETTINGS::REFERRAL::ALWAYS_GIVE_COMMISSION', false) : false);
        $this->migrator->add('referral.enabled', $table_exists ? $this->getOldValue('SETTINGS::REFERRAL::ENABLED', false) : false);
        $this->migrator->add('referral.reward', $table_exists ? $this->getOldValue('SETTINGS::REFERRAL::REWARD') : 100);
        $this->migrator->add('referral.mode', $table_exists ? $this->getOldValue('SETTINGS::REFERRAL:MODE', 'sign-up') : 'sign-up');
        $this->migrator->add('referral.percentage', $table_exists ? $this->getOldValue('SETTINGS::REFERRAL:PERCENTAGE', 100) : 100);
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::REFERRAL::ALLOWED',
                'value' => $this->getNewValue('allowed', 'referral'),
                'type' => 'string',
                'description' => 'The allowed referral types.',
            ],
            [
                'key' => 'SETTINGS::REFERRAL::ALWAYS_GIVE_COMMISSION',
                'value' => $this->getNewValue('always_give_commission', 'referral'),
                'type' => 'boolean',
                'description' => 'Whether to always give commission to the referrer.',
            ],
            [
                'key' => 'SETTINGS::REFERRAL::ENABLED',
                'value' => $this->getNewValue('enabled', 'referral'),
                'type' => 'boolean',
                'description' => 'Whether to enable the referral system.',
            ],
            [
                'key' => 'SETTINGS::REFERRAL::REWARD',
                'value' => $this->getNewValue('reward', 'referral'),
                'type' => 'integer',
                'description' => 'The reward for the referral.',
            ],
            [
                'key' => 'SETTINGS::REFERRAL:MODE',
                'value' => $this->getNewValue('mode', 'referral'),
                'type' => 'string',
                'description' => 'The referral mode.',
            ],
            [
                'key' => 'SETTINGS::REFERRAL:PERCENTAGE',
                'value' => $this->getNewValue('percentage', 'referral'),
                'type' => 'integer',
                'description' => 'The referral percentage.',
            ],
        ]);

        try {
            $this->migrator->delete('referral.allowed');
            $this->migrator->delete('referral.always_give_commission');
            $this->migrator->delete('referral.enabled');
            $this->migrator->delete('referral.reward');
            $this->migrator->delete('referral.mode');
            $this->migrator->delete('referral.percentage');
        } catch (Exception $e) {
            //
        }
    }
}
