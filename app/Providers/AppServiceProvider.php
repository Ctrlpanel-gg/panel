<?php

namespace App\Providers;

use App\Extensions\PaymentGateways\PayPal\PayPalSettings;
use App\Models\UsefulLink;
use App\Settings\MailSettings;
use Exception;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
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

        $settings = $this->app->make(MailSettings::class);
        $settings->setConfig();
    }
}
