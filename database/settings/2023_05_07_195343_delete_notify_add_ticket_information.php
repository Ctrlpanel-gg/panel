<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->delete('ticket.notify');
        $this->migrator->add('ticket.information',  "Can't start your server? Need an additional port? Do you have any other questions? Let us know by opening a ticket.");
    }

    public function down(): void
    {
        $this->migrator->add('ticket.notify', 'all');
        $this->migrator->delete('ticket.information');
    }
};
