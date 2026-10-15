<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('general.currency_format_override', '');
    }

    public function down(): void
    {
        $this->migrator->delete('general.currency_format_override');
    }
};
