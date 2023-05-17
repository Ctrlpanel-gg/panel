<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('coupon.max_uses_per_user', 1);
    }

    public function down(): void
    {
        $this->migrator->delete('coupon.max_uses_per_user');
    }
};
