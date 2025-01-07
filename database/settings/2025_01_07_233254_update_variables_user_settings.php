<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->rename('user.server_limit_after_irl_purchase', 'user.server_limit_increment_after_irl_purchase');
        $this->migrator->rename('user.server_limit_after_verify_discord', 'user.server_limit_increment_after_verify_discord');
        $this->migrator->rename('user.server_limit_after_verify_email', 'user.server_limit_increment_after_verify_email');
    }

    public function down(): void
    {
        $this->migrator->rename('user.server_limit_increment_after_irl_purchase', 'user.server_limit_after_irl_purchase');
        $this->migrator->rename('user.server_limit_increment_after_verify_discord', 'user.server_limit_after_verify_discord');
        $this->migrator->rename('user.server_limit_increment_after_verify_email', 'user.server_limit_after_verify_email');
    }
};
