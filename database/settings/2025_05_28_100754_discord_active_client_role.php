<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('discord.role_for_active_clients', false);
        $this->migrator->add('discord.role_id_for_active_clients', "");
    }

    public function down(): void
    {
        $this->migrator->delete('discord.role_for_active_clients');
        $this->migrator->delete('discord.role_id_for_active_clients');
    }
};
