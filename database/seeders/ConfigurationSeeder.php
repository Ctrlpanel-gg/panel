<?php

namespace Database\Seeders;

use App\Models\Configuration;
use Illuminate\Database\Seeder;

class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //initials
        Configuration::create([
            'key'   => 'INITIAL_CREDITS',
            'value' => '250',
            'type'  => 'integer',
        ]);

        Configuration::create([
            'key'   => 'INITIAL_SERVER_LIMIT',
            'value' => '1',
            'type'  => 'integer',
        ]);

        //verify email event
        Configuration::create([
            'key'   => 'CREDITS_REWARD_AFTER_VERIFY_EMAIL',
            'value' => '250',
            'type'  => 'integer',
        ]);

        Configuration::create([
            'key'   => 'SERVER_LIMIT_REWARD_AFTER_VERIFY_EMAIL',
            'value' => '2',
            'type'  => 'integer',
        ]);

        //verify discord event
        Configuration::create([
            'key'   => 'CREDITS_REWARD_AFTER_VERIFY_DISCORD',
            'value' => '375',
            'type'  => 'integer',
        ]);

        Configuration::create([
            'key'   => 'SERVER_LIMIT_REWARD_AFTER_VERIFY_DISCORD',
            'value' => '2',
            'type'  => 'integer',
        ]);

        Configuration::create([
            'key'   => 'DISCORD_VERIFY_COMMAND',
            'value' => '!verify',
            'type'  => 'string',
        ]);

        //other
        Configuration::create([
            'key'   => 'MINIMUM_REQUIRED_CREDITS_TO_MAKE_SERVER',
            'value' => '50',
            'type'  => 'integer',
        ]);
    }
}
