<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddStripeModeSetting extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('stripe.mode', app()->environment('local') ? 'test' : 'live');
    }

    public function down(): void
    {
        $this->migrator->delete('stripe.mode');
    }
}
