<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('referral.require_email_verification', false);
    }
    public function down(): void
    {
        $this->migrator->delete('referral.require_email_verification');
    }
};
