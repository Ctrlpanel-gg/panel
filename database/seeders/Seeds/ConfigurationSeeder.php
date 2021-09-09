<?php

namespace Database\Seeders\Seeds;

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
        Configuration::firstOrCreate([
            'key' => 'INITIAL_CREDITS',
        ], [
            'value'       => '250',
            'type'        => 'integer',
            'description' => 'The initial amount of credits the user starts with.'
        ]);

        Configuration::firstOrCreate([
            'key' => 'INITIAL_SERVER_LIMIT',
        ], [
            'value'       => '1',
            'type'        => 'integer',
            'description' => 'The initial server limit the user starts with.'
        ]);

        //verify email event
        Configuration::firstOrCreate([
            'key' => 'CREDITS_REWARD_AFTER_VERIFY_EMAIL',
        ], [
            'value'       => '250',
            'type'        => 'integer',
            'description' => 'Increase in credits after the user has verified their email account.'
        ]);

        Configuration::firstOrCreate([
            'key' => 'SERVER_LIMIT_REWARD_AFTER_VERIFY_EMAIL',
        ], [
            'value'       => '2',
            'type'        => 'integer',
            'description' => 'Increase in server limit after the user has verified their email account.'
        ]);

        //verify discord event
        Configuration::firstOrCreate([
            'key' => 'CREDITS_REWARD_AFTER_VERIFY_DISCORD',
        ], [
            'value'       => '375',
            'type'        => 'integer',
            'description' => 'Increase in credits after the user has verified their discord account.'
        ]);

        Configuration::firstOrCreate([
            'key' => 'SERVER_LIMIT_REWARD_AFTER_VERIFY_DISCORD',
        ], [
            'value'       => '2',
            'type'        => 'integer',
            'description' => 'Increase in server limit after the user has verified their discord account.'
        ]);

        //other
        Configuration::firstOrCreate([
            'key' => 'MINIMUM_REQUIRED_CREDITS_TO_MAKE_SERVER',
        ], [
            'value'       => '50',
            'type'        => 'integer',
            'description' => 'The minimum amount of credits the user would need to make a server.'
        ]);

        //purchasing
        Configuration::firstOrCreate([
            'key' => 'SERVER_LIMIT_AFTER_IRL_PURCHASE',
        ], [
            'value'       => '10',
            'type'        => 'integer',
            'description' => 'updates the users server limit to this amount (unless the user already has a higher server limit) after making a purchase with real money, set to 0 to ignore this.',
        ]);


        //force email and discord verification
        Configuration::firstOrCreate([
            'key' => 'FORCE_EMAIL_VERIFICATION',
        ], [
            'value'       => 'false',
            'type'        => 'boolean',
            'description' => 'Force an user to verify the email adress before creating a server / buying credits.'
        ]);

        Configuration::firstOrCreate([
            'key' => 'FORCE_DISCORD_VERIFICATION',
        ], [
            'value'       => 'false',
            'type'        => 'boolean',
            'description' => 'Force an user to link an Discord Account before creating a server / buying credits.'
        ]);

        //disable ip check on register
        Configuration::firstOrCreate([
            'key' => 'REGISTER_IP_CHECK',
        ], [
            'value'       => 'true',
            'type'        => 'boolean',
            'description' => 'Prevent users from making multiple accounts using the same IP address'
        ]);

        //per_page on allocations request
        Configuration::firstOrCreate([
            'key' => 'ALLOCATION_LIMIT',
        ], [
            'value'       => '200',
            'type'        => 'integer',
            'description' => 'The maximum amount of allocations to pull per node for automatic deployment, if more allocations are being used than this limit is set to, no new servers can be created!'
        ]);

        //credits display name
        Configuration::firstOrCreate([
            'key' => 'CREDITS_DISPLAY_NAME',
        ], [
            'value'       => 'Credits',
            'type'        => 'string',
            'description' => 'Set the display name of your currency :)'
        ]);


    }
}
