<?php

use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddStripeWebhookSigningSecretSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->addEncrypted('stripe.webhook_signing_secret', $this->getStripeSetting('endpoint_secret'));
        $this->migrator->addEncrypted('stripe.test_webhook_signing_secret', $this->getStripeSetting('test_endpoint_secret'));
    }

    public function down(): void
    {
        $this->migrator->delete('stripe.webhook_signing_secret');
        $this->migrator->delete('stripe.test_webhook_signing_secret');
    }

    protected function getStripeSetting(string $name): ?string
    {
        $row = DB::table('settings')
            ->where('group', 'stripe')
            ->where('name', $name)
            ->first(['payload']);

        if (!$row || !isset($row->payload)) {
            return null;
        }

        $payload = $row->payload;
        if (!is_string($payload) || $payload === '' || $payload === 'null' || $payload === '""') {
            return null;
        }

        $decoded = json_decode($payload, true);
        if (is_string($decoded) && $decoded !== '') {
            return $decoded;
        }

        $trimmed = trim($payload, '"');
        return $trimmed === '' ? null : $trimmed;
    }
}
