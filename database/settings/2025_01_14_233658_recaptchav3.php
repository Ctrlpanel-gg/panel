<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->delete('general.recaptcha_enabled');
        $this->migrator->add('general.recaptcha_version', 'v2');
    }

    public function down(): void
    {
        $this->migrator->add('general.recaptcha_enabled', false);
        $this->migrator->delete('general.recaptcha_version');
    }
};
