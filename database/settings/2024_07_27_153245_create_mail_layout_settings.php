<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateMailLayoutSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('templates.mail_welcome_subject', 'Welcome to {panel}!');
        $this->migrator->add('templates.mail_welcome_body', 'Hello {user}, welcome to {panel}!');
    }

    public function down(): void
    {
        $this->migrator->delete('templates.mail_welcome_subject');
        $this->migrator->delete('templates.mail_welcome_body');
    }
}
