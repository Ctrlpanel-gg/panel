<?php

namespace App\Http\Middleware;

use App\Models\Configuration;
use Closure;
use Illuminate\Http\Request;

class CreditsDisplayName
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        define('CREDITS_DISPLAY_NAME' , Configuration::getValueByKey('CREDITS_DISPLAY_NAME' , 'Credits'));
        return $next($request);
    }
}
