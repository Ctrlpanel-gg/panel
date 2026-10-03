<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('discord.require_minimum_account_age', false);
        $this->migrator->add('discord.minimum_account_age_days', 30);
    }

    public function down(): void
    {
        $this->migrator->delete('discord.require_minimum_account_age');
        $this->migrator->delete('discord.minimum_account_age_days');
    }
};
