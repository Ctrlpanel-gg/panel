<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('coupon.enabled', true);
        $this->migrator->add('coupon.max_uses_per_user', 1);
        $this->migrator->add('coupon.delete_coupon_on_expires', false);
        $this->migrator->add('coupon.delete_coupon_on_uses_reached', false);
    }

    public function down(): void
    {
        $this->migrator->delete('coupon.enabled');
        $this->migrator->delete('coupon.max_uses_per_user');
        $this->migrator->delete('coupon.delete_coupon_on_expires');
        $this->migrator->delete('coupon.delete_coupon_on_uses_reached');
    }
};
