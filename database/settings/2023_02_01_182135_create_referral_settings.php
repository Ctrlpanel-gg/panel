<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateReferralSettings extends SettingsMigration
{
    public function up(): void
    {
        // Get the user-set configuration values from the old table.
        $this->migrator->add('referral.allowed', ($this->getOldValue('SETTINGS::REFERRAL::ALLOWED') != null) ?: 'client');
        $this->migrator->add('referral.always_give_commission', ($this->getOldValue('SETTINGS::REFERRAL::ALWAYS_GIVE_COMMISSION') != null) ?: false);
        $this->migrator->add('referral.enabled', ($this->getOldValue('SETTINGS::REFERRAL::ENABLED') != null) ?: false);
        $this->migrator->add('referral.reward', ($this->getOldValue('SETTINGS::REFERRAL::REWARD') != null) ?: 100);
        $this->migrator->add('referral.mode', ($this->getOldValue('SETTINGS::REFERRAL:MODE') != null) ?: 'sign-up');
        $this->migrator->add('referral.percentage', ($this->getOldValue('SETTINGS::REFERRAL:PERCENTAGE') != null) ?: 100);
    }

    public function getOldValue(string $key)
    {
        if (DB::table('settings_old')->exists()) {
            return DB::table('settings_old')->where('key', '=', $key)->get(['value']);
        }

        return null;
    }
}