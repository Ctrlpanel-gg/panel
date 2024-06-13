<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

class CreateCryptoBotSettings extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->addEncrypted('cryptobot.api_key', null);
        $this->migrator->addEncrypted('cryptobot.hidden_message', null);
        $this->migrator->add('cryptobot.enabled', false);
    }

    public function down(): void
    {
        $this->migrator->delete('cryptobot.api_key');
        $this->migrator->delete('cryptobot.hidden_message');
        $this->migrator->delete('cryptobot.enabled');
    }
}
