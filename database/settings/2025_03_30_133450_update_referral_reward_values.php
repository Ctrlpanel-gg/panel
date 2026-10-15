<?php

use App\Models\Settings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $reward = Settings::get('referral.reward');

        if ($reward) {
            Settings::set('referral.reward', $reward * 1000);
        }
    }

    public function down(): void
    {
        $reward = Settings::get('referral.reward');

        if ($reward) {
            Settings::set('referral.reward', $reward / 1000);
        }
    }
};
