<?php

namespace App\Providers;

use App\Helpers\CallHomeHelper;
use App\Models\UsefulLink;
use Exception;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
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

        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            $event->extendSocialite('discord', \SocialiteProviders\Discord\Provider::class);
        });

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

        // Force HTTPS if APP_URL is set to https
        if (config('app.url') && parse_url(config('app.url'), PHP_URL_SCHEME) === 'https') {
            URL::forceScheme('https');
        }

        CallHomeHelper::callHomeOnce();

        //get the Github Branch the panel is running on
        $headFile = base_path() . '/.git/HEAD';
        $headFileMissing = false;
        if (file_exists($headFile)) {
            try {
                $stringfromfile = file($headFile);
                $firstLine = $stringfromfile[0]; //get the string from the array
                $explodedstring = explode('/', $firstLine, 3); //seperate out by the "/" in the string
                $branchname = isset($explodedstring[2]) ? trim($explodedstring[2]) : 'unknown';
            } catch (Exception $e) {
                $branchname = 'unknown';
                Log::notice($e);
            }
        } else {
            $branchname = 'unknown';
            $headFileMissing = true;
        }
        config(['BRANCHNAME' => $branchname]);
        view()->share('headFileMissing', $headFileMissing);

        // Do not run this code if no APP_KEY is set
        if (config('app.key') == null)
            return;

        try {
            if (Schema::hasColumn('useful_links', 'position')) {
                $useful_links = UsefulLink::where("position", "like", "%topbar%")->get()->sortby("id");
                view()->share('useful_links', $useful_links);
            }
        } catch (Exception $e) {
            Log::error("Couldnt find useful_links. Probably the installation is not completet. " . $e);
        }
    }
}
