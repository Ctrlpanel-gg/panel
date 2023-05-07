<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;
use Illuminate\Support\Facades\DB;

class CreateReferralSettings extends SettingsMigration
{
    public function up(): void
    {
        $table_exists = DB::table('settings_old')->exists();

        // Get the user-set configuration values from the old table.
        $this->migrator->add('referral.always_give_commission', $table_exists ? $this->getOldValue('SETTINGS::REFERRAL::ALWAYS_GIVE_COMMISSION') : false);
        $this->migrator->add('referral.enabled', $table_exists ? $this->getOldValue('SETTINGS::REFERRAL::ENABLED') : false);
        $this->migrator->add('referral.reward', $table_exists ? $this->getOldValue('SETTINGS::REFERRAL::REWARD') : 100);
        $this->migrator->add('referral.mode', $table_exists ? $this->getOldValue('SETTINGS::REFERRAL:MODE') : 'sign-up');
        $this->migrator->add('referral.percentage', $table_exists ? $this->getOldValue('SETTINGS::REFERRAL:PERCENTAGE') : 100);
    }

    public function down(): void
    {
        DB::table('settings_old')->insert([
            [
                'key' => 'SETTINGS::REFERRAL::ALLOWED',
                'value' => $this->getNewValue('allowed'),
                'type' => 'string',
                'description' => 'The allowed referral types.',
            ],
            [
                'key' => 'SETTINGS::REFERRAL::ALWAYS_GIVE_COMMISSION',
                'value' => $this->getNewValue('always_give_commission'),
                'type' => 'boolean',
                'description' => 'Whether to always give commission to the referrer.',
            ],
            [
                'key' => 'SETTINGS::REFERRAL::ENABLED',
                'value' => $this->getNewValue('enabled'),
                'type' => 'boolean',
                'description' => 'Whether to enable the referral system.',
            ],
            [
                'key' => 'SETTINGS::REFERRAL::REWARD',
                'value' => $this->getNewValue('reward'),
                'type' => 'integer',
                'description' => 'The reward for the referral.',
            ],
            [
                'key' => 'SETTINGS::REFERRAL:MODE',
                'value' => $this->getNewValue('mode'),
                'type' => 'string',
                'description' => 'The referral mode.',
            ],
            [
                'key' => 'SETTINGS::REFERRAL:PERCENTAGE',
                'value' => $this->getNewValue('percentage'),
                'type' => 'integer',
                'description' => 'The referral percentage.',
            ],
        ]);

        $this->migrator->delete('referral.allowed');
        $this->migrator->delete('referral.always_give_commission');
        $this->migrator->delete('referral.enabled');
        $this->migrator->delete('referral.reward');
        $this->migrator->delete('referral.mode');
        $this->migrator->delete('referral.percentage');
    }

    public function getNewValue(string $name)
    {
        $new_value = DB::table('settings')->where([['group', '=', 'referral'], ['name', '=', $name]])->get(['payload'])->first();

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

    public function getOldValue(string $key)
    {
        // Always get the first value of the key.
        $old_value = DB::table('settings_old')->where('key', '=', $key)->get(['value', 'type'])->first();

        // Handle the old values to return without it being a string in all cases.
        if ($old_value->type === "string" || $old_value->type === "text") {
            if (is_null($old_value->value)) {
                return '';
            }

            // Some values have the type string, but their values are boolean.
            if ($old_value->value === "false" || $old_value->value === "true") {
                return filter_var($old_value->value, FILTER_VALIDATE_BOOL);
            }

            return $old_value->value;
        }

        if ($old_value->type === "boolean") {
            return filter_var($old_value->value, FILTER_VALIDATE_BOOL);
        }

        return filter_var($old_value->value, FILTER_VALIDATE_INT);
    }
}
