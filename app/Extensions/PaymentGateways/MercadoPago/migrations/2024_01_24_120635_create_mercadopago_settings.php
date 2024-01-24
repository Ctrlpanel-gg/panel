<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateMercadoPagoSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->addEncrypted('mpago.access_token', null);
        $this->migrator->add('mpago.enabled', false);
    }

    public function down(): void
    {
        $this->migrator->delete('mpago.access_token');
        $this->migrator->delete('mpago.enabled');
    }
}
