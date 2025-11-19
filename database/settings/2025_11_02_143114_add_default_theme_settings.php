<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->migrator->add('theming.force_theme_mode', null);
        $this->migrator->add('theming.default_theme', 'dark');

        $this->migrator->add('theming.primary_100', '#f1f5f9');
        $this->migrator->add('theming.primary_200', '#e2e8f0');
        $this->migrator->add('theming.primary_300', '#cbd5e1');
        $this->migrator->add('theming.primary_400', '#94a3b8');
        $this->migrator->add('theming.primary_500', '#64748b');
        $this->migrator->add('theming.primary_600', '#475569');
        $this->migrator->add('theming.primary_700', '#334155');

        $this->migrator->add('theming.accent_50', '#f0f8ff');
        $this->migrator->add('theming.accent_100', '#e6eaf8');
        $this->migrator->add('theming.accent_200', '#d4d9f7');
        $this->migrator->add('theming.accent_300', '#b8c5f0');
        $this->migrator->add('theming.accent_400', '#7fa3e8');
        $this->migrator->add('theming.accent_500', '#3062a3');
        $this->migrator->add('theming.accent_600', '#2962cc');
        $this->migrator->add('theming.accent_700', '#0e1434');
        $this->migrator->add('theming.accent_800', '#2ef2ff');

        $this->migrator->add('theming.success', '#10b981');
        $this->migrator->add('theming.warning', '#f59e0b');
        $this->migrator->add('theming.info', '#0ea5e9');
        $this->migrator->add('theming.danger', '#ef4444');
        $this->migrator->add('theming.cyan', '#06b6d4');

        $this->migrator->add('theming.gray_50', '#f9fafb');
        $this->migrator->add('theming.gray_100', '#f4f5f7');
        $this->migrator->add('theming.gray_200', '#e5e7eb');
        $this->migrator->add('theming.gray_300', '#d5d6d7');
        $this->migrator->add('theming.gray_400', '#c6c6c6');
        $this->migrator->add('theming.gray_500', '#707275');
        $this->migrator->add('theming.gray_600', '#4c4f52');
        $this->migrator->add('theming.gray_700', '#24262d');
        $this->migrator->add('theming.gray_800', '#1a1c23');
        $this->migrator->add('theming.gray_900', '#121317');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $this->migrator->delete('theming.force_theme_mode');
        $this->migrator->delete('theming.default_theme');

        $this->migrator->delete('theming.primary_100');
        $this->migrator->delete('theming.primary_200');
        $this->migrator->delete('theming.primary_300');
        $this->migrator->delete('theming.primary_400');
        $this->migrator->delete('theming.primary_500');
        $this->migrator->delete('theming.primary_600');
        $this->migrator->delete('theming.primary_700');

        $this->migrator->delete('theming.accent_50');
        $this->migrator->delete('theming.accent_100');
        $this->migrator->delete('theming.accent_200');
        $this->migrator->delete('theming.accent_300');
        $this->migrator->delete('theming.accent_400');
        $this->migrator->delete('theming.accent_500');
        $this->migrator->delete('theming.accent_600');
        $this->migrator->delete('theming.accent_700');
        $this->migrator->delete('theming.accent_800');

        $this->migrator->delete('theming.success');
        $this->migrator->delete('theming.warning');
        $this->migrator->delete('theming.info');
        $this->migrator->delete('theming.danger');
        $this->migrator->delete('theming.cyan');

        $this->migrator->delete('theming.gray_50');
        $this->migrator->delete('theming.gray_100');
        $this->migrator->delete('theming.gray_200');
        $this->migrator->delete('theming.gray_300');
        $this->migrator->delete('theming.gray_400');
        $this->migrator->delete('theming.gray_500');
        $this->migrator->delete('theming.gray_600');
        $this->migrator->delete('theming.gray_700');
        $this->migrator->delete('theming.gray_800');
        $this->migrator->delete('theming.gray_900');
    }
};
