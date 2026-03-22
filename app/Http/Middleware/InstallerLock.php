<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InstallerLock
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (app()->environment('testing')) {
            return $next($request);
        }

        if (! file_exists(base_path() . "/install.lock")) {
            $webInstallerEnabled = filter_var(env('ENABLE_WEB_INSTALLER', false), FILTER_VALIDATE_BOOLEAN);

            if (app()->environment('production') && ! $webInstallerEnabled) {
                abort(503, __('The application is not installed and the web installer is disabled in production.'));
            }

            return redirect('/installer');
        }

        return $next($request);
    }
}
