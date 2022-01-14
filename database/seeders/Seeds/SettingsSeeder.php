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

        //Invoice company name
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::INVOICE:COMPANY_NAME',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'The name of the Company on the Invoices'
        ]);
        //Invoice company address
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::INVOICE:COMPANY_ADDRESS',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'The address of the Company on the Invoices'
        ]);
        //Invoice company phone
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::INVOICE:COMPANY_PHONE',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'The phone number of the Company on the Invoices'
        ]);

        //Invoice company mail
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::INVOICE:COMPANY_MAIL',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'The email address of the Company on the Invoices'
        ]);

        //Invoice VAT
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::INVOICE:COMPANY_VAT',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'The VAT-Number of the Company on the Invoices'
        ]);

        //Invoice Website
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::INVOICE:COMPANY_WEBSITE',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'The Website of the Company on the Invoices'
        ]);

        //Invoice Website
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::INVOICE:PREFIX',
        ], [
            'value' => 'INV',
            'type'  => 'string',
            'description'  => 'The invoice prefix'
        ]);

        //Locale
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::LOCALE:DEFAULT',
        ], [
            'value' => 'en',
            'type'  => 'string',
            'description'  => 'The default Language the dashboard will be shown in'
        ]);
        //Dynamic locale
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::LOCALE:DYNAMIC',
        ], [
            'value' => 'false',
            'type'  => 'boolean',
            'description'  => 'If this is true, the Language will change to the Clients browserlanguage or default.'
        ]);
        //User can change Locale
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::LOCALE:CLIENTS_CAN_CHANGE',
        ], [
            'value' => 'false',
            'type'  => 'boolean',
            'description'  => 'If this is true, the clients will be able to change their Locale.'
        ]);
        //Locale
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::LOCALE:AVAILABLE',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'The available languages'
        ]);
        //Locale
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::LOCALE:DATATABLES',
        ], [
            'value' => 'en-gb',
            'type'  => 'string',
            'description'  => 'The Language of the Datatables. Grab the Language-Codes from here https://datatables.net/plug-ins/i18n/'
        ]);

        Settings::firstOrCreate([
            'key'   => 'SETTINGS::PAYMENTS:PAYPAL:SECRET',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'Your PayPal Secret-Key ( https://developer.paypal.com/docs/integration/direct/rest/)'
        ]);
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::PAYMENTS:PAYPAL:CLIENT_ID',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'Your PayPal Client_ID'
        ]);
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::PAYMENTS:PAYPAL:SANDBOX_SECRET',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'Your PayPal SANDBOX Secret-Key used for testing '
        ]);
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::PAYMENTS:PAYPAL:SANDBOX_CLIENT_ID',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'Your PayPal SANDBOX Client-ID used for testing '
        ]);
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::PAYMENTS:STRIPE:SECRET',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'Your Stripe  Secret-Key  ( https://dashboard.stripe.com/account/apikeys )'
        ]);
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::PAYMENTS:STRIPE:ENDPOINT_SECRET',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'Your Stripe endpoint secret-key'
        ]);
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::PAYMENTS:STRIPE:TEST_SECRET',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'Your Stripe test secret-key'
        ]);
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::PAYMENTS:STRIPE:ENDPOINT_TEST_SECRET',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'Your Stripe endpoint test secret-key'
        ]);
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::PAYMENTS:STRIPE:METHODS',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'Comma seperated list of payment methods that are enabled (https://stripe.com/docs/payments/payment-methods/integration-options)'
        ]);

        Settings::firstOrCreate([
            'key'   => 'SETTINGS::DISCORD:CLIENT_ID',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'Discord API Credentials - https://discordapp.com/developers/applications/'
        ]);

        Settings::firstOrCreate([
            'key'   => 'SETTINGS::DISCORD:CLIENT_SECRET',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'Discord API Credentials - https://discordapp.com/developers/applications/'
        ]);
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::DISCORD:BOT_TOKEN',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'Discord API Credentials - https://discordapp.com/developers/applications/'
        ]);

        Settings::firstOrCreate([
            'key'   => 'SETTINGS::DISCORD:GUILD_ID',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'Discord API Credentials - https://discordapp.com/developers/applications/'
        ]);

        Settings::firstOrCreate([
            'key'   => 'SETTINGS::DISCORD:ROLE_ID',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'Discord role that will be assigned to users when they register'
        ]);
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::DISCORD:INVITE_URL',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'The invite URL to your Discord Server'
        ]);

        Settings::firstOrCreate([
            'key'   => 'SETTINGS::SYSTEM:PTERODACTYL:TOKEN',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'Admin API Token from Pterodactyl Panel - necessary for the Panel to work. The Key needs all read&write permissions!'
        ]);
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::SYSTEM:PTERODACTYL:URL',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'The URL to your Pterodactyl Panel. Must not end with a / '
        ]);
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::MISC:PHPMYADMIN:URL',
        ], [
            'value' => '',
            'type'  => 'string',
            'description'  => 'The URL to your PHPMYADMIN Panel. Must not end with a /, remove to remove database button'
        ]);

        Settings::firstOrCreate([
            'key'   => 'SETTINGS::RECAPTCHA:SITE_KEY',
        ], [
            'value' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
            'type'  => 'string',
            'description'  => 'Google Recaptcha API Credentials - https://www.google.com/recaptcha/admin - reCaptcha V2 (not v3)'
        ]);

        Settings::firstOrCreate([
            'key'   => 'SETTINGS::RECAPTCHA:SECRET_KEY',
        ], [
            'value' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
            'type'  => 'string',
            'description'  => 'Google Recaptcha API Credentials - https://www.google.com/recaptcha/admin - reCaptcha V2 (not v3)'
        ]);
        Settings::firstOrCreate([
            'key'   => 'SETTINGS::RECAPTCHA:ENABLED',
        ], [
            'value' => 'true',
            'type'  => 'boolean',
        ]);
    }
}
