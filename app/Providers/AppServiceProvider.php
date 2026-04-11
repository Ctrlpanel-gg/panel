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
        $branchname = 'unknown';
        $commitHash = 'unknown';

        try {
            $headFilePath = base_path() . '/.git/HEAD';
            if (!file_exists($headFilePath)) {
                $headFileMissing = true;
                throw new Exception('.git/HEAD file not found');
            }

            $fileContent = file($headFilePath);
            if (!$fileContent || empty($fileContent[0])) {
                throw new Exception('.git/HEAD file is empty or unreadable');
            }

            $firstLine = trim($fileContent[0]);

            if (str_starts_with($firstLine, 'ref:')) {
                $ref = trim(str_replace('ref:', '', $firstLine)); // "refs/heads/main"
                $branchname = str_replace('refs/heads/', '', $ref);

                // try loose ref file first
                $refFile = base_path() . '/.git/' . $ref;
                if (file_exists($refFile)) {
                    $commitHash = substr(trim(file_get_contents($refFile)), 0, 7);
                } else {
                    // fallback to packed-refs
                    $packedRefsFile = base_path() . '/.git/packed-refs';
                    if (file_exists($packedRefsFile)) {
                        foreach (file($packedRefsFile) as $line) {
                            $line = trim($line);
                            if (str_ends_with($line, $ref)) {
                                $commitHash = substr(explode(' ', $line)[0], 0, 7);
                                break;
                            }
                        }
                    }
                }
            } else {
                // detached HEAD - hash is directly in HEAD
                $branchname = 'detached';
                $commitHash = substr($firstLine, 0, 7);
            }
        } catch (Exception $e) {
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
