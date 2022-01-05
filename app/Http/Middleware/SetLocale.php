<?php

namespace App\Http\Middleware;

use App\Models\Settings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{

    /**
     *
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
            if (Session::has('locale')) {
                $locale = Session::get('locale', Settings::getValueByKey("SETTINGS::LOCALE:DEFAULT"));
            } else {
                if (Settings::getValueByKey("SETTINGS::LOCALE:DYNAMIC") == "false") {
                    $locale = Settings::getValueByKey("SETTINGS::LOCALE:DEFAULT");
                }else{
                    $locale = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);

                    if (!in_array($locale, json_decode(Settings::getValueByKey("SETTINGS::LOCALE:AVAILABLE")))){
                        $locale = Settings::getValueByKey("SETTINGS::LOCALE:DEFAULT");
                    }

                }
            }
        App::setLocale($locale);

        return $next($request);
    }
}
