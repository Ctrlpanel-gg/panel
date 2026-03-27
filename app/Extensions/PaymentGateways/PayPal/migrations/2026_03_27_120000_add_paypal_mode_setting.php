<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddPaypalModeSetting extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('paypal.mode', app()->environment('local') ? 'sandbox' : 'live');
    }

    public function down(): void
    {
        $this->migrator->delete('paypal.mode');
    }
}
