<?php

namespace App\Http\Middleware;

use App\Settings\LocaleSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    private $locale_settings;

    public function __construct(LocaleSettings $locale_settings)
    {
        $this->locale_settings = $locale_settings;
    }
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Session::has('locale')) {
            $locale = Session::get('locale', $this->locale_settings->default);
        } else {
            if (!$this->locale_settings->dynamic) {
                $locale = $this->locale_settings->default;
            } else {
                $locale = substr($request->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);

                if (! in_array($locale, explode(',', $this->locale_settings->available))) {
                    $locale = $this->locale_settings->default;
                }
            }
        }
        App::setLocale($locale);

        return $next($request);
    }
}
