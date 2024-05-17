<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateMollieSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->addEncrypted('mollie.api_key', null);
        $this->migrator->add('mollie.enabled', false);
    }

    public function down(): void
    {
        $this->migrator->delete('mollie.api_key');
        $this->migrator->delete('mollie.enabled');
    }
}
