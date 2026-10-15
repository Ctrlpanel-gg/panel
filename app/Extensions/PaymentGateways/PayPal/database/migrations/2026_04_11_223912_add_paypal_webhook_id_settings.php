<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddPayPalWebhookIdSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('paypal.webhook_id', null);
        $this->migrator->add('paypal.sandbox_webhook_id', null);
    }

    public function down(): void
    {
        $this->migrator->delete('paypal.webhook_id');
        $this->migrator->delete('paypal.sandbox_webhook_id');
    }
}
