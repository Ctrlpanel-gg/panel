<?php

namespace App\Models;

use Spatie\LaravelSettings\Models\SettingsProperty;
use Spatie\LaravelSettings\SettingsContainer;

class Settings extends SettingsProperty
{
    public static function set(string $property, $value): void
    {
        [$group, $name] = explode('.', $property);

        $setting = self::query()
            ->where('group', $group)
            ->where('name', $name)
            ->first();

        if ($setting) {
            $setting->payload = json_encode($value);
            $setting->save();
        }

        self::clearCache();
    }

    protected static function clearCache(): void
    {
        try {
            app(SettingsContainer::class)->clearCache();
        } catch (\Exception $e) {
            report($e);
        }
    }
}
