<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Remove global min credits setting; per-product minimums now enforce credit requirements
        $this->migrator->delete('user.min_credits_to_make_server');
    }

    public function down(): void
    {
        // Restore default global minimum credits setting for rollback
        $this->migrator->add('user.min_credits_to_make_server', 0);
    }
};
