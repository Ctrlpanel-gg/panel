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

        // get the Git branch and commit the panel is running on
        $headFileMissing = false;

        try {
            $headFilePath = base_path() . '/.git/HEAD';
            if (!file_exists($headFilePath)) {
                $headFileMissing = true;
                throw new Exception('.git/HEAD file not found');
            }

            $firstLine = trim(file($headFilePath)[0]);
            // branch ref in HEAD is format "ref: refs/heads/branchname"
            if (str_starts_with($firstLine, 'ref:')) {
                $branchname = basename(trim(str_replace('ref:', '', $firstLine)));
            } else {
                // detached HEAD; fallback to unknown
                $branchname = 'detached';
            }

            // attempt to obtain commit hash from git command if available
            $possibleCommit = trim(@shell_exec('git -C ' . escapeshellarg(base_path()) . ' rev-parse --short HEAD 2>/dev/null'));
            if ($possibleCommit !== '') {
                $commitHash = $possibleCommit;
            }
        } catch (Exception $e) {
            $branchname = 'unknown';
            $commitHash = 'unknown';
            Log::notice($e);
        }

        config(['BRANCHNAME' => $branchname, 'COMMIT_HASH' => $commitHash]);
        view()->share('headFileMissing', $headFileMissing);

        // Do not run this code if no APP_KEY is set
        if (config('app.key') == null) return;

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
