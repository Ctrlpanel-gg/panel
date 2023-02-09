<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ServerSettings extends Settings
{
    public int $allocation_limit;

    public bool $creation_enabled;

    public bool $enable_upgrade;

    public bool $charge_first_hour;

    public static function group(): string
    {
        return 'server';
    }

    /**
     * Summary of optionTypes
     * Only used for the settings page
     * @return array<array<'type'|'label'|'description'|'options', string|bool|float|int|array<string, string>>>
     */
    public static function getOptionInputData()
    {
        return [
            'allocation_limit' => [
                'label' => 'Allocation Limit',
                'type' => 'number',
                'description' => 'The maximum amount of allocations to pull per node for automatic deployment, if more allocations are being used than this limit is set to, no new servers can be created.',
            ],
            'creation_enabled' => [
                'label' => 'Creation Enabled',
                'type' => 'boolean',
                'description' => 'Whether or not users can create servers.',
            ],
            'enable_upgrade' => [
                'label' => 'Enable Upgrade',
                'type' => 'boolean',
                'description' => 'Whether or not users can upgrade their servers.',
            ],
            'charge_first_hour' => [
                'label' => 'Charge First Hour',
                'type' => 'boolean',
                'description' => 'Whether or not the first hour of a server is charged.',
            ],
        ];
    }
}
