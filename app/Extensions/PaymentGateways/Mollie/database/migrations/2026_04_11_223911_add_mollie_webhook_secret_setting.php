<?php

use Illuminate\Support\Str;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddMollieWebhookSecretSetting extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->addEncrypted('mollie.webhook_secret', Str::random(64));
    }

    public function down(): void
    {
        $this->migrator->delete('mollie.webhook_secret');
    }
}
