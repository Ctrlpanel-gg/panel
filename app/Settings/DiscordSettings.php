<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class DiscordSettings extends Settings
{
    public ?string $bot_token = null;
    public ?string $client_id = null;
    public ?string $client_secret = null;
    public ?string $guild_id = null;
    public ?string $role_id = null;
    public ?bool $role_on_purchase = null;
    public ?string $role_id_on_purchase = null;
    public ?bool $role_for_active_clients = null;
    public ?string $role_id_for_active_clients = null;
    public bool $require_minimum_account_age = false;
    public int $minimum_account_age_days = 30;

    public static function group(): string
    {
        return 'discord';
    }

    /**
     * Summary of validations array
     * @return array<string, string>
     */
    public static function getValidations()
    {
        return [
            'bot_token' => 'nullable|string',
            'client_id' => 'nullable|string',
            'client_secret' => 'nullable|string',
            'guild_id' => 'nullable|string',
            'role_id' => 'nullable|string',
            'role_on_purchase' => 'nullable|string',
            'role_id_on_purchase' => 'nullable|string',
            'role_for_active_clients' => 'nullable|string',
            'role_id_for_active_clients' => 'nullable|string',
            'require_minimum_account_age' => 'nullable|string',
            'minimum_account_age_days' => 'required|numeric',
        ];
    }

    /**
     * Summary of optionInputData array
     * Only used for the settings page
     * @return array<array<'type'|'label'|'description'|'options', string|bool|float|int|array<string, string>>>
     */
    public static function getOptionInputData()
    {
        return [
            'category_icon' => 'fab fa-discord',
            'position' => 5,
            'category_description' => 'Connect your Discord bot and manage the roles assigned to users',
            'sections' => [
                'bot' => [
                    'label' => 'Bot',
                    'description' => 'Credentials for your Discord bot',
                ],
                'guild' => [
                    'label' => 'Guild',
                    'description' => 'The Discord server your bot operates on',
                ],
                'roles' => [
                    'label' => 'Roles',
                    'description' => 'Which roles should be assigned to your users',
                ],
                'age' => [
                    'label' => 'Account Age',
                    'description' => 'Minimum age requirement of a Discord account to be linked',
                ],
            ],
            'bot_token' => [
                'label' => 'Bot Token',
                'type' => 'secret',
                'description' => 'The bot token for your Discord bot',
                'section' => 'bot',
            ],
            'client_id' => [
                'label' => 'Client ID',
                'type' => 'string',
                'description' => 'The client ID for your Discord bot',
                'section' => 'bot',
            ],
            'client_secret' => [
                'label' => 'Client Secret',
                'type' => 'secret',
                'description' => 'The client secret for your Discord bot',
                'section' => 'bot',
            ],
            'guild_id' => [
                'label' => 'Guild ID',
                'type' => 'string',
                'description' => 'The guild ID for your Discord server',
                'section' => 'guild',
            ],
            'role_id' => [
                'label' => 'Role ID after linking',
                'type' => 'string',
                'description' => 'ID of the Discord-Role to give users when linking their discord Account',
                'section' => 'roles',
            ],
            'require_minimum_account_age' => [
                'label' => 'Require Minimum Account Age',
                'type' => 'boolean',
                'description' => 'Reject Discord accounts younger than the minimum age below',
                'section' => 'age',
            ],
            'minimum_account_age_days' => [
                'label' => 'Minimum Account Age (days)',
                'type' => 'number',
                'description' => 'A Discord account must be at least this days old to be linked',
                'section' => 'age',
            ],
            'role_for_active_clients' => [
                'label' => 'Role for active Clients',
                'type' => 'select',
                'options' => [
                    '0' => 'Disabled',
                    '1' => 'Enabled'
                ],
                'description' => 'Give the user a role when creating/owning a Server (removes when user has no active servers)',
                'section' => 'roles',
            ],
            'role_id_for_active_clients' => [
                'label' => 'Role ID for active Clients',
                'type' => 'string',
                'description' => 'ID of the Discord-Role to give users when they have an active server',
                'section' => 'roles',
            ],
            'role_on_purchase' => [
                'label' => 'Role on Credit-purchase',
                'type' => 'select',
                'options' => [
                    '0' => 'Disabled',
                    '1' => 'Enabled'
                ],
                'description' => 'Give the user a role when they buy credits with real money',
                'section' => 'roles',
            ],
            'role_id_on_purchase' => [
                'label' => 'Role ID on Purchase',
                'type' => 'string',
                'description' => 'ID of the Discord-Role to give users when they purchase credits with real money',
                'section' => 'roles',
            ],
        ];
    }
}
