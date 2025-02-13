<?php

namespace App\Classes;

use Exception;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelSettings\Migrations\SettingsMigration;


abstract class LegacySettingsMigration extends SettingsMigration
{
    public function getNewValue(string $name, string $group)
    {
        $new_value = DB::table('settings')->where([['group', '=', $group], ['name', '=', $name]])->get(['payload'])->first();

        if (is_null($new_value) || is_null($new_value->payload)) {
            return null;
        }

        // Some keys returns '""' as a value.
        if ($new_value->payload === '""') {
            return null;
        }


        // remove the quotes from the string
        if (substr($new_value->payload, 0, 1) === '"' && substr($new_value->payload, -1) === '"') {
            return substr($new_value->payload, 1, -1);
        }

        return $new_value->payload;
    }

    /**
     * Get the old value from the settings_old table.
     * @param string $key The key to get the value from table.
     * @param int|string|bool|null $default The default value to return if the value is null. If value is not nullable, a default must be provided.
     */
    public function getOldValue(string $key,  int|string|bool|null $default = null)
    {
        $old_value = DB::table('settings_old')->where('key', '=', $key)->get(['value', 'type'])->first();

        if (is_null($old_value) || is_null($old_value->value)) {
            return $default;
        }

        switch ($old_value->type) {
            case 'string':
            case 'text':
                // Edgecase: The value is a boolean, but it's stored as a string.
                if ($old_value->value === "false" || $old_value->value === "true") {
                    return filter_var($old_value->value, FILTER_VALIDATE_BOOL);
                }
                return $old_value->value;
            case 'boolean':
                return filter_var($old_value->value, FILTER_VALIDATE_BOOL);
            case 'integer':
                return filter_var($old_value->value, FILTER_VALIDATE_INT);
            default:
                throw new Exception("Unknown type: {$old_value->type}");
        }
    }
}
