<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->delete('discord.invite_url');
    }

    public function down(): void
    {
        $this->migrator->add('discord.invite_url');
    }
};
