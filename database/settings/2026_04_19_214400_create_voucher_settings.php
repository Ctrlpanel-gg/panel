<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('voucher.delete_voucher_on_expires', false);
        $this->migrator->add('voucher.delete_voucher_on_uses_reached', false);
    }

    public function down(): void
    {
        $this->migrator->delete('voucher.delete_voucher_on_expires');
        $this->migrator->delete('voucher.delete_voucher_on_uses_reached');
    }
};
