<?php

namespace App\Providers;

use App\Models\Settings;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Paginator::useBootstrap();
        Schema::defaultStringLength(191);

        Validator::extend('multiple_date_format', function ($attribute, $value, $parameters, $validator) {
            $ok = true;
            $result = [];

            // iterate through all formats
            foreach ($parameters as $parameter) {
                //validate with laravels standard date format validation
                $result[] = $validator->validateDateFormat($attribute, $value, [$parameter]);
            }

            //if none of result array is true. it sets ok to false
            if (!in_array(true, $result)) {
                $ok = false;
                $validator->setCustomMessages(['multiple_date_format' => 'The format must be one of ' . join(",", $parameters)]);
            }

            return $ok;
        });

        // Set Discord-API Config
        config(['services.discord.client_id' => Settings::getValueByKey('SETTINGS::DISCORD:CLIENT_ID')]);
        config(['services.discord.client_secret' => Settings::getValueByKey('SETTINGS::DISCORD:CLIENT_SECRET')]);
        config(['services.discord.SETTINGS::SYSTEM:SERVER_CREATE_CHARGE_FIRST_HOUR' => 'test']);

        // Set Recaptcha API Config
        config(['recaptcha.api_site_key' => Settings::getValueByKey('SETTINGS::RECAPTCHA:SITE_KEY')]);
        config(['recaptcha.api_secret_key' => Settings::getValueByKey('SETTINGS::RECAPTCHA:SECRET_KEY')]);

        // Set all configs from database
        $settings = Settings::all();
        foreach ($settings as $setting) {
            config([$setting->key => $setting->value]);
        }
    }
}
