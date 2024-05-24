<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('server.location_description_enabled',false);
    }

    public function down(): void
    {
        $this->migrator->delete('server.location_description_enabled');
    }
};
