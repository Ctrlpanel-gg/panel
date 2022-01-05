<?php

namespace Database\Seeders\Seeds;

use App\Models\Settings;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //initials
        Settings::firstOrCreate([
            'key' => 'SETTINGS::USER:INITIAL_CREDITS',
        ], [
            'value'       => '250',
            'type'        => 'integer',
            'description' => 'The initial amount of credits the user starts with.'
        ]);

        Settings::firstOrCreate([
            'key' => 'SETTINGS::USER:NITIAL_SERVER_LIMIT',
        ], [
            'value'       => '1',
            'type'        => 'integer',
            'description' => 'The initial server limit the user starts with.'
        ]);

        //verify email event
        Settings::firstOrCreate([
            'key' => 'SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_EMAIL',
        ], [
            'value'       => '250',
            'type'        => 'integer',
            'description' => 'Increase in credits after the user has verified their email account.'
        ]);

        Settings::firstOrCreate([
            'key' => 'SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_EMAIL',
        ], [
            'value'       => '2',
            'type'        => 'integer',
            'description' => 'Increase in server limit after the user has verified their email account.'
        ]);

        //verify discord event
        Settings::firstOrCreate([
            'key' => 'SETTINGS::USER:CREDITS_REWARD_AFTER_VERIFY_DISCORD',
        ], [
            'value'       => '375',
            'type'        => 'integer',
            'description' => 'Increase in credits after the user has verified their discord account.'
        ]);

        Settings::firstOrCreate([
            'key' => 'SETTINGS::USER:SERVER_LIMIT_REWARD_AFTER_VERIFY_DISCORD',
        ], [
            'value'       => '2',
            'type'        => 'integer',
            'description' => 'Increase in server limit after the user has verified their discord account.'
        ]);

        //other
        Settings::firstOrCreate([
            'key' => 'SETTINGS::USER:MINIMUM_REQUIRED_CREDITS_TO_MAKE_SERVER',
        ], [
            'value'       => '50',
            'type'        => 'integer',
            'description' => 'The minimum amount of credits the user would need to make a server.'
        ]);

        //purchasing
        Settings::firstOrCreate([
            'key' => 'SETTINGS::USER:SERVER_LIMIT_AFTER_IRL_PURCHASE',
        ], [
            'value'       => '10',
            'type'        => 'integer',
            'description' => 'updates the users server limit to this amount (unless the user already has a higher server limit) after making a purchase with real money, set to 0 to ignore this.',
        ]);


        //force email and discord verification
        Settings::firstOrCreate([
            'key' => 'SETTINGS::USER:FORCE_EMAIL_VERIFICATION',
        ], [
            'value'       => 'false',
            'type'        => 'boolean',
            'description' => 'Force an user to verify the email adress before creating a server / buying credits.'
        ]);

        Settings::firstOrCreate([
            'key' => 'SETTINGS::USER:FORCE_DISCORD_VERIFICATION',
        ], [
            'value'       => 'false',
            'type'        => 'boolean',
            'description' => 'Force an user to link an Discord Account before creating a server / buying credits.'
        ]);

        //disable ip check on register
        Settings::firstOrCreate([
            'key' => 'SETTINGS::SYSTEM:REGISTER_IP_CHECK',
        ], [
            'value'       => 'true',
            'type'        => 'boolean',
            'description' => 'Prevent users from making multiple accounts using the same IP address'
        ]);

        //per_page on allocations request
        Settings::firstOrCreate([
            'key' => 'SETTINGS::SERVER:ALLOCATION_LIMIT',
        ], [
            'value'       => '200',
            'type'        => 'integer',
            'description' => 'The maximum amount of allocations to pull per node for automatic deployment, if more allocations are being used than this limit is set to, no new servers can be created!'
        ]);

        //credits display name
        Settings::firstOrCreate([
            'key' => 'SETTINGS::SYSTEM:CREDITS_DISPLAY_NAME',
        ], [
            'value'       => 'Credits',
            'type'        => 'string',
            'description' => 'Set the display name of your currency :)'
        ]);

        //credits display name
        Settings::firstOrCreate([
            'key' => 'SETTINGS::SYSTEM:SERVER_CREATE_CHARGE_FIRST_HOUR',
        ], [
            'value'       => 'true',
            'type'        => 'boolean',
            'description' => 'Charges the first hour worth of credits upon creating a server.'
        ]);
        //sales tax
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::PAYMENTS:SALES_TAX',
        ], [
            'value' => '0',
            'type'  => 'integer',
            'description'  => 'The %-value of tax that will be added to the product price on checkout'
        ]);

    }
}
