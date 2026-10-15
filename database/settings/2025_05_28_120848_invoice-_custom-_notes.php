<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('invoice.additional_notes', "");
    }

    public function down(): void{
        $this->migrator->delete('invoice.additional_notes');
    }
};
