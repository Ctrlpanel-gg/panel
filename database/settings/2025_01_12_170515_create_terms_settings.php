<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('terms.terms_of_service', '');
        $this->migrator->add('terms.privacy_policy', '');
        $this->migrator->add('terms.imprint', '');
    }

    public function down(): void
    {
        $this->migrator->delete('terms.terms_of_service');
        $this->migrator->delete('terms.privacy_policy');
        $this->migrator->delete('terms.imprint');
    }
};
