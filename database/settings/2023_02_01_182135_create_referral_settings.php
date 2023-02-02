<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateReferralSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('referral.allowed', $table_exists ? $this->getOldValue('SETTINGS::REFERRAL::ALLOWED') : 'client');
        $this->migrator->add('referral.always_give_commission', $table_exists ? $this->getOldValue('SETTINGS::REFERRAL::ALWAYS_GIVE_COMMISSION') : false);
        $this->migrator->add('referral.enabled', $table_exists ? $this->getOldValue('SETTINGS::REFERRAL::ENABLED') : false);
        $this->migrator->add('referral.reward', $table_exists ? $this->getOldValue('SETTINGS::REFERRAL::REWARD') : 100);
        $this->migrator->add('referral.mode', $table_exists ? $this->getOldValue('SETTINGS::REFERRAL:MODE') : 'sign-up');
        $this->migrator->add('referral.percentage', $table_exists ? $this->getOldValue('SETTINGS::REFERRAL:PERCENTAGE') : 100);
    }

    public function getOldValue(string $key)
    {
        // Always get the first value of the key.
        return DB::table('settings_old')->where('key', '=', $key)->get(['value'])->first()->value;
    }
}