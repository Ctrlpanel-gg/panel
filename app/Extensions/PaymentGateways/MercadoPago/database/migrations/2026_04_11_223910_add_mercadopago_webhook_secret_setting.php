<?php

use Illuminate\Support\Str;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddMercadoPagoWebhookSecretSetting extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->addEncrypted('mercadopago.webhook_secret', Str::random(64));
    }

    public function down(): void
    {
        $this->migrator->delete('mercadopago.webhook_secret');
    }
}
