<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreatePayPalSettings extends SettingsMigration
{
    public function up(): void
    {

        $this->migrator->addEncrypted('paypal.client_id', "1234");
        $this->migrator->addEncrypted('paypal.client_secret', "123456");
    }

    public function down(): void
    {
        $this->migrator->delete('paypal.client_id');
        $this->migrator->delete('paypal.client_secret');
    }
}
