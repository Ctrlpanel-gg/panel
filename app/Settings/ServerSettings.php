<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ServerSettings extends Settings
{
    public bool $creation_enabled = false;
    public bool $enable_upgrade = false;
    public bool $location_description_enabled = false;
    public int $allocation_limit = 0;

    public static function group(): string
    {
        return 'server';
    }

    /**
     * Summary of validations array
     * @return array<string, string>
     */
    public static function getValidations()
    {
        return [
            'creation_enabled' => 'nullable|string',
            'enable_upgrade' => 'nullable|string',
            'allocation_limit' => 'required|integer|min:0',
            'location_description_enabled' => 'nullable|string',
        ];
    }

    /**
     * Summary of optionTypes
     * Only used for the settings page
     * @return array<array<'type'|'label'|'description'|'options', string|bool|float|int|array<string, string>>>
     */
    public static function getOptionInputData()
    {
        return [
            'category_icon' => 'fas fa-server',
            'position' => 11,
            'creation_enabled' => [
                'label' => 'Creation Enabled',
                'type' => 'boolean',
                'description' => 'Enable the user server creation.',
            ],
            'enable_upgrade' => [
                'label' => 'Server Upgrade Enabled',
                'type' => 'boolean',
                'description' => 'Enable the server upgrade feature.',
            ],
            'allocation_limit' => [
                'label' => 'Allocation Limit',
                'type' => 'number',
                'description' => 'The maximum amount of allocations to pull per node for automatic deployment, if more allocations are being used than this limit is set to, no new servers can be created.',
            ],
            'location_description_enabled' => [
                'label' => 'Enable Location Description',
                'type' => 'boolean',
                'description' => 'Enable the location description field on the server creation page.',
            ],
        ];
    }
}
