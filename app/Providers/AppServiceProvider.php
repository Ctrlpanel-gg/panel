<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Spatie\QueryBuilder\QueryBuilderRequest;

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
        QueryBuilderRequest::setArrayValueDelimiter('|');

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
    }
}
