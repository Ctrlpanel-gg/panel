<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Remove the global per-user coupon limit setting
        $this->migrator->delete('coupon.max_uses_per_user');
    }

    public function down(): void
    {
        // Restore the removed setting (rollback)
        $this->migrator->add('coupon.max_uses_per_user', null);
    }
};
