<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class AddDiscordSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('discord.role_on_purchase', null);
        $this->migrator->add('discord.role_id_on_purchase', null);
    }

    public function down(): void
    {
        $this->migrator->delete('discord.role_on_purchase');
        $this->migrator->delete('discord.role_id_on_purchase');
    }
}
