<?php

namespace App\Providers;

use App\Models\Settings;
use App\Models\UsefulLink;
use Exception;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Qirolab\Theme\Theme;

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
                $validator->setCustomMessages(['multiple_date_format' => 'The format must be one of ' . implode(',', $parameters)]);
            }

            return $ok;
        });

        try {
            if (Schema::hasColumn('useful_links', 'position')) {
                $useful_links = UsefulLink::where("position", "like", "%topbar%")->get()->sortby("id");
                view()->share('useful_links', $useful_links);
            }
        } catch (Exception $e) {
            Log::error("Couldnt find useful_links. Probably the installation is not completet. " . $e);
        }

        //only run if the installer has been executed
        try {
            $settings = Settings::all();
            // Set all configs from database
            foreach ($settings as $setting) {
                config([$setting->key => $setting->value]);
            }

            if (!file_exists(base_path('themes') . "/" . config("SETTINGS::SYSTEM:THEME"))) {
                config(['SETTINGS::SYSTEM:THEME' => "default"]);
            }

            if (config('SETTINGS::SYSTEM:THEME') && config('SETTINGS::SYSTEM:THEME') !== config('theme.active')) {
                Theme::set(config("SETTINGS::SYSTEM:THEME", "default"), "default");
            } else {
                Theme::set("default", "default");
            }

            // Set Mail Config
            //only update config if mail settings have changed in DB
            if (
                config('mail.default') != config('SETTINGS:MAIL:MAILER') ||
                config('mail.mailers.smtp.host') != config('SETTINGS:MAIL:HOST') ||
                config('mail.mailers.smtp.port') != config('SETTINGS:MAIL:PORT') ||
                config('mail.mailers.smtp.username') != config('SETTINGS:MAIL:USERNAME') ||
                config('mail.mailers.smtp.password') != config('SETTINGS:MAIL:PASSWORD') ||
                config('mail.mailers.smtp.encryption') != config('SETTINGS:MAIL:ENCRYPTION') ||
                config('mail.from.address') != config('SETTINGS:MAIL:FROM_ADDRESS') ||
                config('mail.from.name') != config('SETTINGS:MAIL:FROM_NAME')
            ) {
                config(['mail.default' => config('SETTINGS::MAIL:MAILER')]);
                config(['mail.mailers.smtp' => [
                    'transport' => 'smtp',
                    'host' => config('SETTINGS::MAIL:HOST'),
                    'port' => config('SETTINGS::MAIL:PORT'),
                    'encryption' => config('SETTINGS::MAIL:ENCRYPTION'),
                    'username' => config('SETTINGS::MAIL:USERNAME'),
                    'password' => config('SETTINGS::MAIL:PASSWORD'),
                    'timeout' => null,
                    'auth_mode' => null,
                ]]);
                config(['mail.from' => ['address' => config('SETTINGS::MAIL:FROM_ADDRESS'), 'name' => config('SETTINGS::MAIL:FROM_NAME')]]);

                Artisan::call('queue:restart');
            }

            // Set Recaptcha API Config
            // Load recaptcha package if recaptcha is enabled
            if (config('SETTINGS::RECAPTCHA:ENABLED') == 'true') {
                $this->app->register(\Biscolab\ReCaptcha\ReCaptchaServiceProvider::class);
            }

            //only update config if recaptcha settings have changed in DB
            if (
                config('recaptcha.api_site_key') != config('SETTINGS::RECAPTCHA:SITE_KEY') ||
                config('recaptcha.api_secret_key') != config('SETTINGS::RECAPTCHA:SECRET_KEY')
            ) {
                config(['recaptcha.api_site_key' => config('SETTINGS::RECAPTCHA:SITE_KEY')]);
                config(['recaptcha.api_secret_key' => config('SETTINGS::RECAPTCHA:SECRET_KEY')]);

                Artisan::call('config:clear');
                Artisan::call('cache:clear');
            }

            try {
                $stringfromfile = file(base_path() . '/.git/HEAD');

                $firstLine = $stringfromfile[0]; //get the string from the array

                $explodedstring = explode('/', $firstLine, 3); //seperate out by the "/" in the string

                $branchname = $explodedstring[2]; //get the one that is always the branch name
            } catch (Exception $e) {
                $branchname = 'unknown';
                Log::notice($e);
            }
            config(['BRANCHNAME' => $branchname]);

            // Set Discord-API Config
            config(['services.discord.client_id' => config('SETTINGS::DISCORD:CLIENT_ID')]);
            config(['services.discord.client_secret' => config('SETTINGS::DISCORD:CLIENT_SECRET')]);
        } catch (Exception $e) {
            error_log('Settings Error: Could not load settings from database. The Installation probably is not done yet.');
            error_log($e);
            Log::error('Settings Error: Could not load settings from database. The Installation probably is not done yet.');
            Log::error($e);
        }
    }
}
