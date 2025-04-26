<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateMercadoPagoSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->addEncrypted('mercadopago.access_token', null);
        $this->migrator->add('mercadopago.enabled', false);
    }

    public function down(): void
    {
        $this->migrator->delete('mercadopago.access_token');
        $this->migrator->delete('mercadopago.enabled');
    }
}
